const USE_WP_REST = 1
const USE_FAKE_STREAM = 0

const USE_ALGOLIA = LLMSettings?.dataLoader === 'algolia'
const USE_TYPESENSE = LLMSettings?.dataLoader === 'typesense'

const DISABLE = LLMSettings?.isDisabled 
const WP_API_URL = LLMSettings?.apiUrl;
const WP_NONCE   = LLMSettings?.nonce;

const USE_MATOMO = LLMSettings?.enableMatomo

const TYPESENSE_API_KEY = LLMSettings?.typesenseSearchApiKey 
const TYPESENSE_HOST = LLMSettings?.typesenseUrl
const TYPESENSE_COLLECTION = LLMSettings?.typesenseCollection

const ALGOLIA_APPLICATION_ID = LLMSettings?.algoliaAppId 
const ALGOLIA_API_KEY = LLMSettings?.algoliaSearchApiKey 
const ALGOLIA_INDEX_NAME = LLMSettings?.algoliaIndex 

const JS_ADDON_ENABLED = LLMSettings?.algoliaIndexJsAddonIsEnabled


const TARGET_SELECTOR = JS_ADDON_ENABLED 
  ? '#searchbox' 
  : '.t-searchform .c-form'

  const INPUT_SELECTOR = JS_ADDON_ENABLED 
  ? '#input_searchboxfield' 
  : '#input_search-form--field'


const debounce = (fn, waitMs) => {
  let timeout;
  return (...args) => {
    clearTimeout(timeout);
    timeout = setTimeout(() => fn(...args), waitMs);
  }
}

const createFakeDataLoader = () => async (query) => [
  {
    title: 'Bygga altan',
    content:
      'För att bygga en altan behöver du i de flesta fall bygglov. Det finns dock några undantag där du inte behöver bygglov för att bygga en altan.',
    url: 'https://www.helsingborg.se',
  },
]

const createFakeAIGenerator = () => async (data, query) =>
  `<h5>Here is the user query:</h5><div>${query}</div><h5>Here are the search results: </h5></br><div>${JSON.stringify(
    data,
    null,
    4
  )}</div>`

const createFakeStreamGenerator = () => async (data, query, onChunk) => {
  const fullAnswer = await createFakeAIGenerator()(data, query)
  const chunkSize = 20
  for (let i = 0; i < fullAnswer.length; i += chunkSize) {
    onChunk(fullAnswer.slice(i, i + chunkSize))
    await new Promise((resolve) => setTimeout(resolve, 100))
  }
}

const createAlgoliaLoader = (appID, apiKey, indexName) => async (query) =>
  fetch(`https://${appID}-dsn.algolia.net/1/indexes/${indexName}/query`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-Algolia-API-Key': apiKey,
      'X-Algolia-Application-Id': appID,
    },
    body: JSON.stringify({ params: `query=${encodeURIComponent(query)}` }),
  })
    .then((res) => res.json())
    .then((data) =>
      data?.hits?.map((item, i) => ({
        title: item?.post_title ?? '',
        content: item?.content ?? '',
        url: item?.permalink ?? '',
        reference: i + 1,
      })) ?? []
    )

const createTypesenseLoader = (host, apiKey, collectionName, searchType = 'search') => async (query) => {
  const searchTypes = {
    'search': () => {
      const searchParams = new URLSearchParams({
        q: query,
        query_by: 'content,post_title',
        per_page: 10,
      })
      return fetch(`${host}/collections/${collectionName}/documents/search?${searchParams}`, {
        method: 'GET',
        headers: {
          'X-TYPESENSE-API-KEY': apiKey
        }
      })
      .then(res => res.json())
      .then(data => 
        data?.hits?.map((hit, i) => ({
          title: hit.document?.post_title ?? '',
          content: hit.document?.content ?? '',
          url: hit.document?.permalink ?? '',
          reference: i + 1
        })) ?? []
      )
    },
    'hybrid': () => {
      return fetch(`${host}/multi_search`, {
        method: 'POST',
        headers: {
          'X-TYPESENSE-API-KEY': apiKey,
          'Content-Type': 'application/json' // Required for JSON body
        },
        body: JSON.stringify({ // Convert object to JSON string
          searches: [
            {
              collection: collectionName,
              q: query,
              query_by: "content,post_title,embedding",
              sort_by: "_text_match:desc",
              exclude_fields: "embedding",
              prefix: false
            }
          ]
        })
      }) 
      .then(res => res.json())
      .then(data => 
        {
          const output = data?.results[0]?.hits?.map((hit, i) => ({
            title: hit.document?.post_title ?? '',
            content: hit.document?.content ?? '',
            url: hit.document?.permalink ?? '',
            reference: i + 1
          })) ?? []
          return output
        }
      )
    }
  }

  return searchTypes[searchType]()
    .catch(error => {
      console.error('Typesense error:', error)
      return []
    })
}
    

const ensureResponseOk = async (response) =>
  response.ok ? response : Promise.reject(new Error(await response.text()))

const safeParse = (str) => {
  try {
    return JSON.parse(str)
  } catch (e) {
    console.error('Error parsing stream chunk', e)
    return null
  }
}

const createWpRestGenerator = (apiUrl, nonce) => async (data, query, onChunk) => {
    const promptPayload = `Query: ${query}\nData: ${JSON.stringify(data)}`;
  
    const response = await fetch(apiUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce':      nonce,
      },
      body: JSON.stringify({ query: promptPayload }),
    });

    if (!response.ok) {
      throw new Error(await response.text());
    }
  
    const reader  = response.body.getReader();
    const decoder = new TextDecoder('utf-8');
  
    while (true) {
      const { done, value } = await reader.read();
      if (done) break;
      const str = decoder.decode(value, { stream: true });
      onChunk(str);
    }
  }
  
const createFakeTracker = () => (eventCategory, eventAction, eventLabel, eventValue = null) => {
  console.log(`[Fake Tracker] ${eventCategory} - ${eventAction}: ${eventLabel} ${[...eventValue !== null ? [eventValue] : []]}`)
}

const createMatomoTracker = () => (eventCategory, eventAction, eventLabel, eventValue = null) => {
  if (typeof window._paq === 'undefined') {
    console.error('Matomo is not loaded')
    return
  }
  window._paq.push(['trackEvent', eventCategory, eventAction, eventLabel, ...eventValue !== null ? [eventValue] : []])
}

const wrapGenerator = (generator) => (data, query, onChunk) =>
  generator.length >= 3
    ? generator(data, query, onChunk)
    : generator(data, query).then((result) => (onChunk(result), result))

const createAccumulator = (render) => {
  let acc = ''
  return (chunk) => ((acc += chunk), render(acc))
}

const createWrapper = (target) => {
  const div = document.createElement('div')
  div.id = 'llm-wrapper'
  div.classList.add(
    'llm-wrapper',
  )
  target?.insertAdjacentElement('afterend', div)
  return div
}

const createPaper = (target) => {
  const div = document.createElement('div')
  div.classList.add(
    'c-paper',
    'c-paper--padding-3',
    'u-color__bg--complementary-lighter'
  )

  target?.appendChild(div)
  return div
}

const createRender = (target) => {
  const answerContainer = document.createElement('div')
  answerContainer.classList.add('generated-answer')
  target?.appendChild(answerContainer)
  return (content) => (answerContainer.innerHTML = content)
}

const createButton = (label, onClick, target, icon) => {
  const btn = document.createElement('button')
  btn.classList.add('c-button', 'c-button__filled', 'c-button__filled--default', 'c-button--md', 'u-margin__right--1')
  btn.innerHTML = `
  <span class="c-button__label">
      <span class="c-button__label-text">
          <i aria-hidden="true" class="c-icon c-icon--color-primary c-icon--size-inherit material-icons">
              ${icon}
          </i>                
      </span>
  </span>
  `
  btn.addEventListener('click', onClick)
  target?.appendChild(btn)
  return btn
}

const createButtonContainer = (target) => {
  const div = document.createElement('div')
  target?.appendChild(div)
  return div
}

const createShowFeedbackHandler = (target, onSubmitFeedback) => {
  const div = document.createElement('div')
  div.style.display = 'none'
  div.classList.add('feedback', 'u-margin__top--2')

  const buttonContainer = createButtonContainer(div)

  const handleFeedback = (feedbackType) => {
    onSubmitFeedback(feedbackType)
    buttonContainer.innerHTML = 'Tack för din feedback!'
  }

  createButton('Like', () => handleFeedback('positive'), buttonContainer, 'thumb_up')
  createButton('Dislike', () => handleFeedback('negative'), buttonContainer, 'thumb_down')

  target?.appendChild(div)
  const showFeedback = () => {
    div.style.display = ''
  }
  return showFeedback
}

const renderWidget = (target, onSubmitFeedback) => {
  const div = document.querySelector('#llm-wrapper') ?? createWrapper(target)
  div.innerHTML = ''
  const paper = createPaper(div)
  const render = createRender(paper)
  const showFeedback = createShowFeedbackHandler(paper, onSubmitFeedback)
  return { render, showFeedback }
}

const loadData = USE_TYPESENSE 
  ? createTypesenseLoader(TYPESENSE_HOST, TYPESENSE_API_KEY, TYPESENSE_COLLECTION)
  : USE_ALGOLIA
    ? createAlgoliaLoader(
        ALGOLIA_APPLICATION_ID,
        ALGOLIA_API_KEY,
        ALGOLIA_INDEX_NAME
      )
    : createFakeDataLoader()

const generateAnswer = wrapGenerator(
    USE_WP_REST
        ? createWpRestGenerator(WP_API_URL, WP_NONCE)
        : USE_FAKE_STREAM
            ? createFakeStreamGenerator()
            : createFakeAIGenerator()
    );

const onTrackEvent = USE_MATOMO
  ? createMatomoTracker()
  : createFakeTracker()

const renderWidgetFromQuery = (query) => {
  if (!query || query === '') return

  const { render, showFeedback } = renderWidget(
    document.querySelector(TARGET_SELECTOR),
    (feedback) => onTrackEvent('LLM Test', 'Feedback', feedback, feedback == 'positive' ? 1 : 0)
  )

  render('Loading...')

  onTrackEvent('LLM Test', 'Stream', 'Begin')

  loadData(query)
    .then((data) =>
      generateAnswer(data, query, createAccumulator(render))
    )
    .then(() => onTrackEvent('LLM Test', 'Stream', 'Complete'))
    .then(showFeedback)
    .catch((error) => {
      onTrackEvent('LLM Test', 'Error', error)
      render('Något gick fel..')
    })
  }

const renderWidgetFromQueryWithDebounce = debounce(renderWidgetFromQuery, 500)

const app = () => {
  if (JS_ADDON_ENABLED) {
    const inputElement = document.querySelector(INPUT_SELECTOR)
  
    inputElement?.addEventListener('keydown', () => {
      const el = document.querySelector('#llm-wrapper')
      if (el) el.innerHTML = ''
    })
    
    inputElement?.addEventListener('keyup', e => {
      renderWidgetFromQueryWithDebounce(e?.target?.value || '')
    })
  
    inputElement?.addEventListener('change', e => 
      renderWidgetFromQuery(e?.target?.value || '')
    )
  }

  renderWidgetFromQuery(new URLSearchParams(window.location.search).get('s') || '')
}

document.addEventListener('DOMContentLoaded', !DISABLE ? app : () => {})


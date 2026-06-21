# Image Generation Prompt: ch07_microservices_library.php Logic Flow

## Prompt

Create a clean, vertical flowchart diagram illustrating the logic flow of a PHP PSR-15 middleware pipeline for a microservices library API. Use a white background. Use a harmonious set of soft pastel colors for the boxes: pale sky blue for input/output boxes, soft mint green for process/middleware boxes, light lavender for decision/routing boxes, pale peach for validation boxes, and pale rose for error response boxes. Use dark charcoal text throughout for readability. Rounded rectangle boxes, thin grey connector arrows with arrowheads.

The diagram should flow top to bottom with these stages:

---

**[pale sky blue box, top]**
INCOMING HTTP POST REQUEST
(curl or HTTP client)
port 8889

↓ arrow labeled "all requests"

**[soft mint green box]**
LOGGER MIDDLEWARE
Logs method, path, timestamp
to middleware.log

↓ arrow labeled "passes through"

**[light lavender diamond/decision box]**
PATH-BASED ROUTER
What is the request URL path?

Three arrows branch out from this diamond horizontally, labeled:
- Left arrow: POST /distance
- Center arrow: POST /translate
- Right arrow: POST /capital
- Far right arrow: any other path

---

**LEFT BRANCH — POST /distance**

↓

**[pale peach box]**
DISTANCE HANDLER
Validate inputs:
• city_from (required)
• city_to (required)
• iso2_from (valid ISO2 code)
• iso2_to (valid ISO2 code)
• units: km | miles

↓ arrow: "validation fails"

**[pale rose box]**
JSON ERROR RESPONSE
HTTP 400
{ "success": false,
  "data": "error message" }

↓ arrow: "validation passes"

**[soft mint green box]**
GenAiConnect::genAIcall()
Prompt: distance from city_from
to city_to in given units

↓

**[pale sky blue box]**
JSON SUCCESS RESPONSE
HTTP 200
{ "success": true,
  "data": "distance value" }

---

**CENTER BRANCH — POST /translate**

↓

**[pale peach box]**
TRANSLATE HANDLER
Validate inputs:
• lang_from (ISO 639-1 code)
• lang_to (ISO 639-1 code)
• phrase (non-empty, ≤ 1024 chars)

↓ arrow: "validation fails"

**[pale rose box]**
JSON ERROR RESPONSE
HTTP 400
{ "success": false,
  "data": "error message" }

↓ arrow: "validation passes"

**[soft mint green box]**
GenAiConnect::genAIcall()
Prompt: translate phrase
from lang_from to lang_to

↓

**[pale sky blue box]**
JSON SUCCESS RESPONSE
HTTP 200
{ "success": true,
  "data": "translated phrase" }

---

**RIGHT BRANCH — POST /capital**

↓

**[pale peach box]**
CAPITAL HANDLER
Validate inputs:
• iso2_or_name
  (ISO2 code OR country name)

↓ arrow: "validation fails"

**[pale rose box]**
JSON ERROR RESPONSE
HTTP 400
{ "success": false,
  "data": "error message" }

↓ arrow: "validation passes"

**[soft mint green box]**
GenAiConnect::genAIcall()
Prompt: capital city of
given country

↓

**[pale sky blue box]**
JSON SUCCESS RESPONSE
HTTP 200
{ "success": true,
  "data": "capital city name" }

---

**FAR RIGHT BRANCH — unmatched path**

↓

**[pale rose box]**
404 NOT FOUND HANDLER
HTTP 404
"Not Found"

---

All three success response boxes and the 404 box converge at the bottom into a single:

**[pale sky blue box, bottom]**
RESPONSE EMITTED TO CLIENT
(SapiEmitter)

---

Style notes:
- Title at the top of the image: "PHP Middleware Pipeline — Microservices Library API"
- Subtitle below title: "ch07_microservices_library.php"
- Font: clean sans-serif (e.g. Inter or similar)
- Make the diagram wide enough (landscape orientation, 1792×1024) to show all four branches side by side without crowding
- Label each handler box with its PHP class name in small italic text below the box title: Distance, Translate, Capital
- Keep all text concise and readable at 1792×1024 resolution

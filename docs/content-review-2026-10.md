# Content review and competitor research: October 2026

How the industry, service and main pages were reviewed, what the research found, and what changed.
The changes are applied by `App\Support\Site\Content\ContentReview` and run from migration
`2026_10_14_002100_content_review` on existing installs and from `gtech:seed-content` on new ones.
A field changes only while it still holds the text it had before the review, so edits made in the
panel are kept.

## Method

- **UK search results** (Ahrefs, country GB): who ranks in the top 8, People Also Ask questions and
  AI Overview citations for each sector's main search.
- **UK search volume and difficulty** for about 50 candidate keywords, to target the words people
  actually use.
- **Direct page fetches were blocked** by the sandbox network policy. The structure of competitor
  pages was therefore taken from what ranks and from the questions Google shows, not from reading
  their pages.
- **Rules for the copy:** answer the intent, name the sector's entities and avoid keyword stuffing.
  Each secondary term with real demand appears once, in a sentence where it reads naturally. Terms
  without measurable demand were dropped.

## What ranks (UK, October 2026)

| Search | Monthly UK searches (KD) | Who ranks / what Google shows |
|---|---|---|
| ecommerce marketing agency | 1,900 (36) | Specialist agencies (Adnomics, Bring Digital, DigitalB), "top agencies" lists; PAA: what it is, top UK agencies, average fees |
| b2b marketing agency | 1,500 (67) | B2B Marketing (publisher), ArmstrongB2B, Found, directories; PAA: what it does, cost, best UK agencies |
| b2b lead generation agency | 1,200 (6) | Low competition for a high-intent term |
| travel marketing agency / travel seo agency | 800 (0) / 800 (1) | Weak results (PR firms, a staging site): a clear opportunity; PAA: what it is, how to market a travel business |
| saas marketing agency / saas seo agency | 700 (9) / 400 (0) | Rocket SaaS, SEO Works, Gripped, roundups |
| automotive marketing agency | 500 (53) | Visarc, AutoWebDesign, Reddit, blogs; local pack of small specialists |
| hospitality / hotel / restaurant marketing agency | 450 (41) / 300 (3) / 400 (45) | Arise, D-EDGE, Up Hotel Agency, hotel tech roundups; AI Overview cites Found, Up Hotel Agency, Peak Media; PAA: how much hotels spend on marketing |
| property marketing agency / estate agent marketing | 450 (42) / 200 (18) | PropertyStream, Estate Agent Marketing, print and content firms; PAA: best strategies for estate agents |
| bespoke software development | 1,300 (0) | UK searchers say "bespoke" more than "custom"; PAA: what it is, cost, examples, advantages |
| facebook ads agency | 2,700 (0) | Large demand, weak competition |
| social media marketing agency | 2,900 (3) | We Are Social, Soap Media, roundups; PAA: cost, what it does, 5-5-5 rule |
| web development company | 2,200 (0) | Weak, thin pages; PAA: what they do, cost |
| ux agency / ui ux design agency | 800 (0) / 600 (34) | Specialist London studios and roundups; PAA: what agencies charge |

## Findings

1. **Industry pages were thin and repetitive.** They had about 750 words, and every heading began
   "X Marketing: …", which is the kind of keyword pattern that reads as stuffing. They had no cost
   section and no "which option" comparison, although People Also Ask asks both for every sector.
2. **Pages were missing sector entities.** Competitors and AI Overviews talk about OTAs and
   Booking.com, Rightmove and instant valuations, AutoTrader and FCA finance rules, MQL/SQL and ABM,
   CAC, LTV and G2, ATOL and ABTA, and Klaviyo and Merchant Center.
3. **Service pages were in good shape.** The gaps were wording UK searchers use ("bespoke
   software", "Facebook ads agency", "web development company") and a few high-demand questions.
4. **UI/UX and Print had no keyword map entry**, so their checks fell back to weaker terms.
5. **About and Industries needed entity details.** About lacked the founding year, the London
   address and the full company name in its copy, and the Industries page had no FAQs.

## Changes

**Industry pages (7):**
- New natural headings.
- A rewritten overview that answers "What is … marketing?".
- Four rewritten sections with sector entities.
- New sections: "Who we work with", a comparison table (for example OTA vs direct,
  portals vs your own website, marketplaces vs dealer marketing, inbound vs outbound, sales-led vs
  product-led, travel SEO vs paid search, marketplaces vs your own store) and a cost section with
  typical UK ranges.
- Three People Also Ask FAQs each.
- New titles, descriptions and opening text.
- Keyword map entries extended with real secondary terms and entities.

**Service pages:**
- Custom software: "Bespoke Software Development" overview, plus the examples, advantages and cost
  questions.
- Facebook: "Facebook ads agency" in the title and description, plus two FAQs.
- Social media: cost and 5-5-5 rule FAQs.
- Web design and development: "company" wording, plus "what they do" and cost FAQs.
- UI/UX and Print: new titles, descriptions and opening text, keyword map entries and FAQs.
- WordPress, mobile apps and website maintenance: FAQs.
- Secondary terms and entities added to the keyword map for these and for e-commerce development
  and API integration.

**Main pages:**
- About: Global Tech Digital name, 2014 founding, the Brick Lane address, a corrected company story
  (websites first), fuller FAQ answers and a "When was GTech Digital founded?" FAQ.
- Industries: three FAQs, with FAQPage schema.

**Audit tool:** "UI/UX design" and "UI UX design" now count as the same phrase.

## Please check

The cost ranges are typical UK market ranges, written as "most … typically". Change them in the
panel if your own prices differ. The same applies to the figures quoted in FAQs, such as OTA
commission of 15 to 25 per cent.

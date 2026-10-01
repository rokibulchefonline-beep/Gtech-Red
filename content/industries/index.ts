import type { ServiceContent } from '../types';
import automotive from './automotive';
import b2b from './b2b-marketing';
import ecommerce from './e-commerce';
import education from './education';
import finance from './finance';
import healthcare from './healthcare';
import hospitality from './hospitality-hotels';
import realEstate from './real-estate';
import saas from './technology-saas';
import travel from './travel';

// Long-form industry pages, keyed by industry slug (see `industries` in lib/data.ts).
export const industryContent: Record<string, ServiceContent> = Object.fromEntries(
  [ecommerce, education, b2b, automotive, healthcare, hospitality, travel, realEstate, finance, saas].map((c) => [c.slug, c]),
);

import type { ServiceContent } from '../types';
import seo from './search-engine-optimization';

// Long-form service pages. Add each new page here.
export const serviceContent: Record<string, ServiceContent> = {
  [seo.slug]: seo,
};

import type { ServiceContent } from '../types';
import contentMarketing from './content-marketing';
import digitalAdvertising from './digital-advertising';
import googleAds from './google-ads';
import paidMedia from './paid-media';
import reputation from './reputation-management';
import seo from './search-engine-optimization';
import backlinks from './seo-backlinks';
import facebook from './facebook-marketing';
import instagram from './instagram-marketing';
import linkedin from './linkedin-marketing';
import pinterest from './pinterest-marketing';
import social from './social-media-marketing';
import tiktok from './tiktok-marketing';

// Long-form service pages. Add each new page here.
export const serviceContent: Record<string, ServiceContent> = Object.fromEntries(
  [seo, googleAds, reputation, contentMarketing, backlinks, digitalAdvertising, paidMedia, social, facebook, instagram, linkedin, tiktok, pinterest].map((c) => [c.slug, c]),
);

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
import webDesignDev from './web-design-development';
import wordpress from './wordpress-development';
import php from './php-development';
import cms from './cms-development';
import laravel from './laravel-development';
import maintenance from './website-maintenance';
import ecommerce from './ecommerce-development';
import websiteDesign from './website-design';
import localSeo from './local-seo';
import ecommerceSeo from './ecommerce-seo';

// Long-form service pages. Add each new page here.
export const serviceContent: Record<string, ServiceContent> = Object.fromEntries(
  [seo, googleAds, reputation, contentMarketing, backlinks, digitalAdvertising, paidMedia, social, facebook, instagram, linkedin, tiktok, pinterest, webDesignDev, wordpress, php, cms, laravel, maintenance, ecommerce, websiteDesign, localSeo, ecommerceSeo].map((c) => [c.slug, c]),
);

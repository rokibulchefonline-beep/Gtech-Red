import { defineCloudflareConfig } from '@opennextjs/cloudflare';
import staticAssetsIncrementalCache from '@opennextjs/cloudflare/overrides/incremental-cache/static-assets-incremental-cache';

export default {
  ...defineCloudflareConfig({
    // Every page is pre-rendered at build time. Serving that HTML from static assets (and
    // intercepting the cache before Next.js loads) keeps CPU per request tiny, which keeps the
    // Worker inside the Workers Free plan's CPU limit.
    incrementalCache: staticAssetsIncrementalCache,
    enableCacheInterception: true,
  }),
  // `npm run build` switches to the OpenNext build on Cloudflare, so OpenNext must call
  // Next.js directly here to avoid running itself again.
  buildCommand: 'npx next build',
};

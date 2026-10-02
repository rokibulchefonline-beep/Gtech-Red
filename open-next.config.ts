import { defineCloudflareConfig } from '@opennextjs/cloudflare';

export default {
  ...defineCloudflareConfig(),
  // `npm run build` switches to the OpenNext build on Cloudflare, so OpenNext must call
  // Next.js directly here to avoid running itself again.
  buildCommand: 'npx next build',
};

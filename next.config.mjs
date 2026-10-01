/** @type {import('next').NextConfig} */
const nextConfig = {
  async redirects() {
    return [
      { source: '/quote', destination: '/contact', permanent: true },
      { source: '/blog', destination: '/blogs', permanent: true },
      { source: '/blog/:slug', destination: '/blogs/:slug', permanent: true },
      // industry pages were retired; send old links to the services hub
      { source: '/industries', destination: '/services', permanent: true },
      { source: '/industries/:slug', destination: '/services', permanent: true },
      // old /services/{group}/{item} -> /services/{item}
      { source: '/services/:group/:item', destination: '/services/:item', permanent: true },
    ];
  },
};

export default nextConfig;

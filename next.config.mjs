/** @type {import('next').NextConfig} */
const nextConfig = {
  async redirects() {
    // When the Laravel backend is live, the old admin sends people to the new panel.
    const panel = process.env.LARAVEL_API_URL ? `${process.env.LARAVEL_API_URL.replace(/\/$/, '')}/admin` : '';
    return [
      ...(panel ? [{ source: '/admin', destination: panel, permanent: false }, { source: '/admin/:path*', destination: panel, permanent: false }] : []),
      { source: '/quote', destination: '/contact', permanent: true },
      { source: '/blog', destination: '/blogs', permanent: true },
      { source: '/blog/:slug', destination: '/blogs/:slug', permanent: true },
      // old /services/{group}/{item} -> /services/{item}
      { source: '/services/:group/:item', destination: '/services/:item', permanent: true },
    ];
  },
};

export default nextConfig;

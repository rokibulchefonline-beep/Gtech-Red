import { existsSync } from 'node:fs';
import path from 'node:path';

// Partner logos. Upload the PNGs to public/partners/ with these file names and the
// site uses them automatically; until then they load from the original URLs.
const remote = 'https://www.clue.com.au/wp-content/uploads/2024/10';
const logos = [
  ['Google Partner', 'Google-Partner.png'], ['Meta Business Partner', 'Meta-1.png'], ['LinkedIn', 'LinkedIn.png'],
  ['TikTok Marketing Partner', 'TikTok-Partners.png'], ['Brevo Partner', 'Brevo.png'], ['Shopify Partner', 'Shopify.png'],
  ['Klaviyo Partner', 'Klaviyo.png'], ['Google Analytics', 'Google-Analytics.png'],
];
const src = (file: string) =>
  existsSync(path.join(process.cwd(), 'public', 'partners', file)) ? `/partners/${file}` : `${remote}/${file}`;

export default function PartnerStrip() {
  return (
    <section className="pstrip" aria-label="Our partners">
      <div className="wrap">
        <h2>Proud to work with</h2>
        <div className="pstrip-row">
          {logos.map(([name, file]) => (
            // eslint-disable-next-line @next/next/no-img-element
            <img key={file} src={src(file)} alt={name} loading="lazy" />
          ))}
        </div>
      </div>
    </section>
  );
}

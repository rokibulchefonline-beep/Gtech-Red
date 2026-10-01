// Platform partner logos (files in public/partners/).
const logos = [
  ['Google Partner', 'Google-Partner.png'], ['Meta Business Partner', 'Meta-1.png'], ['LinkedIn', 'LinkedIn.png'],
  ['TikTok Marketing Partner', 'TikTok-Partners.png'], ['Brevo Partner', 'Brevo.png'], ['Shopify Partner', 'Shopify.png'],
  ['Klaviyo Partner', 'Klaviyo.png'], ['Google Analytics', 'Google-Analytics.png'],
];

export default function PartnerStrip() {
  return (
    <section className="pstrip" aria-label="Our partners">
      <div className="wrap">
        <h2>Proud to work with</h2>
        <div className="pstrip-row">
          {logos.map(([name, file]) => (
            <div key={file} className="pstrip-tile">
              {/* eslint-disable-next-line @next/next/no-img-element */}
              <img src={`/partners/${file}`} alt={name} loading="lazy" />
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}

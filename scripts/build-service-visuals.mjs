// Renders the animated service visuals to public/services/*.webp
// Run: npm run visuals   (options: --preview to write one PNG frame per scene)
import { mkdirSync, writeFileSync } from 'node:fs';
import sharp from 'sharp';
import { scenes } from './visuals-scenes.mjs';

const FPS = 20, SECONDS = 3.6, FRAMES = Math.round(FPS * SECONDS);
const preview = process.argv.includes('--preview');
const only = process.argv.find((a) => a.startsWith('--only='))?.slice(7);
const out = new URL('../public/services/', import.meta.url);
mkdirSync(out, { recursive: true });

for (const [name, scene] of Object.entries(scenes)) {
  if (only && only !== name) continue;
  if (preview) {
    const png = await sharp(Buffer.from(scene(0.82))).png().toBuffer();
    writeFileSync(new URL(`${name}-preview.png`, out), png);
    continue;
  }
  const frames = [];
  for (let i = 0; i < FRAMES; i++) {
    frames.push(await sharp(Buffer.from(scene(i / FRAMES))).raw().toBuffer({ resolveWithObject: true }));
  }
  const { width, height, channels } = frames[0].info;
  const stacked = Buffer.concat(frames.map((f) => f.data));
  const file = new URL(`${name}.webp`, out);
  await sharp(stacked, { raw: { width, height: height * FRAMES, channels, pageHeight: height } })
    .webp({ nearLossless: true, quality: 85, effort: 5, loop: 0, delay: Array(FRAMES).fill(2000 / FPS) /* half speed */ })
    .toFile(file.pathname);
  console.log(name, 'done');
}

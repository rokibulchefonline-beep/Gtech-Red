// Copies the website's icon set (lib/icon-data.ts) to the Laravel app so Blade renders the same SVGs.
import { writeFileSync } from 'fs';
import { iconData } from '../lib/icon-data';
writeFileSync(new URL('../backend/resources/data/icons.json', import.meta.url), JSON.stringify(iconData));
console.log(Object.keys(iconData).length, 'icons');

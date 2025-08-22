// generate-sitemap.js
import fs from 'fs';
import axios from 'axios';

const API_URL = 'https://api.devgang.online/api/sitemap-data';
const BASE_URL = 'https://devgang.online';

async function generateSitemap() {
    const { data } = await axios.get(API_URL);

    let urls = [
        '/', '/gioi-thieu', '/lien-he', '/gio-hang', '/tin-tuc', '/san-pham'
    ];

    data.products.forEach(slug => {
        urls.push(`/san-pham/${slug}`);
    });

    data.blogs.forEach(slug => {
        urls.push(`/tin-tuc/${slug}`);
    });

    data.pages.forEach(slug => {
        urls.push(`/trang/${slug}`);
    });

    const sitemap = `<?xml version="1.0" encoding="UTF-8"?>
    <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    ${urls
            .map(
                (url) => `
        <url>
        <loc>${BASE_URL}${url}</loc>
        </url>`
            )
            .join('')}
    </urlset>`;

    fs.writeFileSync('./dist/sitemap.xml', sitemap, 'utf8');
    console.log('✅ Sitemap generated at dist/sitemap.xml');
}

generateSitemap();

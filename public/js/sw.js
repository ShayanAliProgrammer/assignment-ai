importScripts(
  "https://storage.googleapis.com/workbox-cdn/releases/6.5.4/workbox-sw.js"
);

if (workbox) {
  console.log("✅ Workbox loaded");

  workbox.skipWaiting();
  workbox.core.clientsClaim();

  // Precache manifest (optional)
  workbox.precaching.precacheAndRoute(self.__WB_MANIFEST || []);

  // 📦 Cache CSS and JS - stale while revalidate
  workbox.routing.registerRoute(
    ({ request }) =>
      request.destination === "style" || request.destination === "script",
    new workbox.strategies.StaleWhileRevalidate({
      cacheName: "assets-css-js",
      plugins: [
        new workbox.expiration.ExpirationPlugin({
          maxEntries: 50,
          maxAgeSeconds: 7 * 24 * 60 * 60,
        }),
      ],
    })
  );

  // 🖼️ Cache images - cache first
  workbox.routing.registerRoute(
    ({ request }) => request.destination === "image",
    new workbox.strategies.CacheFirst({
      cacheName: "assets-images",
      plugins: [
        new workbox.expiration.ExpirationPlugin({
          maxEntries: 60,
          maxAgeSeconds: 30 * 24 * 60 * 60,
        }),
      ],
    })
  );

  // 📄 Cache only specific HTML pages
  const htmlPagesToCache = ["/"];

  workbox.routing.registerRoute(
    ({ request, url }) => {
      return (
        request.destination === "document" &&
        htmlPagesToCache.includes(url.pathname)
      );
    },
    new workbox.strategies.NetworkFirst({
      cacheName: "pages-html",
      plugins: [
        new workbox.expiration.ExpirationPlugin({
          maxEntries: 10,
          maxAgeSeconds: 24 * 60 * 60, // 1 day
        }),
      ],
    })
  );
} else {
  console.log("❌ Workbox failed to load");
}

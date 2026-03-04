"use strict";

const CACHE_KEY = "sw-1772611047";
const OFFLINE_URL = "/offline.html";

self.addEventListener("install", (event) => {
    // Precache the offline fallback page so it is always available.
    // If the file does not exist the install still succeeds.
    event.waitUntil(
        caches
            .open(CACHE_KEY)
            .then((cache) => cache.add(OFFLINE_URL).catch(() => {}))
            .then(() => self.skipWaiting()),
    );
});

self.addEventListener("activate", (event) => {
    event.waitUntil(
        caches
            .keys()
            .then((keys) =>
                Promise.all(
                    keys
                        .filter((key) => key !== CACHE_KEY)
                        .map((key) => caches.delete(key)),
                ),
            )
            .then(() => self.clients.claim()),
    );
});

/**
 * Only cache a response when it is a valid, non-opaque 2xx response.
 * Caching opaque responses (cross-origin without CORS) or error responses
 * would poison the cache with unusable entries.
 */
function isCacheable(response) {
    return response && response.status === 200 && response.type !== "opaque";
}

self.addEventListener("fetch", (event) => {
    if (event.request.method !== "GET") return;

    // Range requests (e.g. video/audio streaming chunks) must bypass the cache —
    // returning a full cached response for a partial request breaks media seeking.
    if (event.request.headers.get("Range")) {
        event.respondWith(fetch(event.request));
        return;
    }

    // Inertia XHR requests carry an X-Inertia header and must always go to
    // the network — caching their JSON responses would cause stale page data.
    if (event.request.headers.get("X-Inertia")) {
        event.respondWith(fetch(event.request));
        return;
    }

    // Livewire component requests must always go to the network.
    if (event.request.headers.get("X-Livewire")) {
        event.respondWith(fetch(event.request));
        return;
    }

    // Generic XHR/fetch requests (e.g. X-Requested-With) bypass the cache.
    if (event.request.headers.get("X-Requested-With")) {
        event.respondWith(fetch(event.request));
        return;
    }

    // Use network-first for navigation requests (HTML pages) so the server
    // always has the final say. Fall back to the offline page when offline.
    if (event.request.mode === "navigate") {
        event.respondWith(
            fetch(event.request).catch(() => caches.match(OFFLINE_URL)),
        );
        return;
    }

    // Cache-first for static assets (JS, CSS, images, fonts, etc.).
    // Only store valid responses; fall back gracefully when offline.
    event.respondWith(
        caches.match(event.request).then((cached) => {
            if (cached) return cached;

            return fetch(event.request)
                .then((response) => {
                    if (isCacheable(response)) {
                        const clone = response.clone();

                        caches
                            .open(CACHE_KEY)
                            .then((cache) => cache.put(event.request, clone));
                    }

                    return response;
                })
                .catch(() => {
                    // Return a simple 503 for assets that cannot be served offline.
                    return new Response("Service unavailable", {
                        status: 503,
                        statusText: "Service Unavailable",
                    });
                });
        }),
    );
});

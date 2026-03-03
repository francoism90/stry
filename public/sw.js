"use strict";

const CACHE_NAME = "pwa-cache-v1";
const OFFLINE_URL = "/offline.html";

self.addEventListener("install", (event) => {
    // Precache the offline fallback page so it is always available.
    event.waitUntil(
        caches
            .open(CACHE_NAME)
            .then((cache) => cache.add(OFFLINE_URL))
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
                        .filter((key) => key !== CACHE_NAME)
                        .map((key) => caches.delete(key)),
                ),
            )
            .then(() => self.clients.claim()),
    );
});

self.addEventListener("fetch", (event) => {
    if (event.request.method !== "GET") return;

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

    // Use network-first for navigation requests (HTML pages) so the server
    // always has the final say. Fall back to the offline page when offline.
    if (event.request.mode === "navigate") {
        event.respondWith(
            fetch(event.request).catch(() => caches.match(OFFLINE_URL)),
        );
        return;
    }

    // Cache-first for static assets (JS, CSS, images, fonts, etc.).
    event.respondWith(
        caches.match(event.request).then(
            (cached) =>
                cached ??
                fetch(event.request).then((response) => {
                    const clone = response.clone();
                    caches
                        .open(CACHE_NAME)
                        .then((cache) => cache.put(event.request, clone));
                    return response;
                }),
        ),
    );
});

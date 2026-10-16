---
title: Requirements
---

These are the checks for this host. Problems stop the installer. Warnings don't, and neither do checks marked with a question mark, which the installer can't see from inside its container.

stry's `stry-horizon` service uses `/dev/dri` for hardware-accelerated transcoding, so the server needs a GPU that provides it.

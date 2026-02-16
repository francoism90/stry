#!/bin/bash

ab-av1 auto-encode \
  -i input.mp4 \
  --encoder av1_vaapi \
  --min-vmaf 95 \
  --max-encoded-percent 150 \
  --enc-input hwaccel=vaapi \
  --enc-input hwaccel_output_format=vaapi

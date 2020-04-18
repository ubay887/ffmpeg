from __future__ import absolute_import

import ffmpeg
import os
import sys

from Utils import Utils

INPUT_FOLDER = "../input/"

try:
    INPUT_FILE_NAME = os.listdir(INPUT_FOLDER)[0]
    INPUT_FILE_PATH = INPUT_FOLDER + INPUT_FILE_NAME
except Exception:
    print("/input is empty!")

#  Put videos in input folder and get the result

input_args = {
    # "hwaccel": "nvdec",
    # "vcodec": "h264_cuvid",
    # "c:v": "h264_cuvid",
    # "vcodec": "libx264"
}

preFile_input_args = {
    # "hwaccel": "nvdec",
    # "vcodec": "h264_cuvid",
    # "c:v": "h264_cuvid",
    # "vcodec": "libx264"
}

LIBX = "libx264"
if len(sys.argv) == 6:
    INPUT_FILE_PATH = sys.argv[1]
    INPUT_FILE_NAME = os.path.basename(INPUT_FILE_PATH)
    TRIM_START = float(sys.argv[2])
    TRIM_END = float(sys.argv[3])
    OVERLAY_CHANNEL = sys.argv[4]
    LIBX = sys.argv[5]

    input_args["ss"] = Utils.sToTimeFormat(TRIM_START, "%H:%M:%S.%f")  # start "00:01:02.500"
    input_args["t"] = Utils.sToTimeFormat(TRIM_END - TRIM_START, "%H:%M:%S.%f")  # duration

    if OVERLAY_CHANNEL == "NewOldMovies":
        PRE_FILE = "assets/NewOldMovies.mp4"
        OVERLAY_FILE = ffmpeg.input("assets/overlayNewOldMovies.png")
    elif OVERLAY_CHANNEL == "MovieLifeSmall":
        PRE_FILE = "assets/MovieLife.mp4"
        OVERLAY_FILE = ffmpeg.input('assets/overlayMovieLifeSmall.png')
    else:
        PRE_FILE = "assets/MovieLife.mp4"
        OVERLAY_FILE = ffmpeg.input('assets/overlayMovieLifeBig.png')

print(INPUT_FILE_NAME)

output_args = {
    # "vcodec": "hevc_nvenc",
    "vcodec": LIBX,
    "c:v": LIBX,
    "preset": "fast",  # ultrafast - superfast - veryfast - faster - fast - medium(default preset) - slow -
    # slower - veryslow - placebo
    "r": 24,
    "crf": 21,
    "b:v": "800k",
    "ac": 1,  # Mono
    # "b:a": "128k",
    "acodec": "aac",  # copy
}

preFileStream = ffmpeg.input(PRE_FILE, **preFile_input_args)
inputStream = ffmpeg.input(INPUT_FILE_PATH, **input_args)
a1 = preFileStream.audio
a2 = inputStream.audio

inputStream = ffmpeg.filter(inputStream, 'scale', size='1920x1080', force_original_aspect_ratio='decrease')
inputStream = ffmpeg.filter(inputStream, 'pad', '1920', '1080', '(ow-iw)/2', '(oh-ih)/2')
inputStream = ffmpeg.overlay(inputStream, OVERLAY_FILE)
inputStream = ffmpeg.filter(inputStream, "fade", type='in', start_time=0, duration=1)
# inputStream = ffmpeg.filter(inputStream, "fade", type='out', duration=1)

stream = ffmpeg.concat(preFileStream, a1, inputStream, a2, v=1, a=1)

OUTPUT_PATH = "../output/" + "[" + Utils.sToTimeFormat(TRIM_START, "%H:%M:%S.%f") + "-" + Utils.sToTimeFormat(TRIM_END,
                                                                                                              "%H:%M:%S.%f") + "]" + INPUT_FILE_NAME
if not os.path.exists("../output/"):
    os.makedirs("../output/")
# if os.path.exists(OUTPUT_PATH):
#  os.remove(OUTPUT_PATH)

stream = ffmpeg.output(stream, OUTPUT_PATH, **output_args)
stream = ffmpeg.overwrite_output(stream)
ffmpeg.run(stream)
print(ffmpeg.compile(stream))

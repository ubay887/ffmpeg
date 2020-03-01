import ffmpeg
import os
import sys

from . import utils

PRE_FILE = "assets/MovieLife.mp4"
INPUT_FOLDER = "input/"
OVERLAY_FILE = ffmpeg.input('assets/overlay.png')

INPUT_FILE_NAME = os.listdir(INPUT_FOLDER)[0]
#  Put videos in input folder and get the result

input_args = {
    "hwaccel": "nvdec",
    "vcodec": "h264_cuvid",
    "c:v": "h264_cuvid",
}

if len(sys.argv) == 5:
    INPUT_FILE_NAME = sys.argv[1]
    TRIM_START = sys.argv[1]
    TRIM_END = sys.argv[2]
    OVERLAY_CHANNEL = sys.argv[3]

    input_args["ss"] = utils.sToTimeFormat(TRIM_START, "%H:%M:%S.%f"),  # start "00:01:02.500"
    input_args["t"] = utils.sToTimeFormat(TRIM_END - TRIM_START, "%H:%M:%S.%f")  # duration

print(INPUT_FILE_NAME)

output_args = {
    "vcodec": "hevc_nvenc",
    "c:v": "libx265",
    "preset": "fast",  # ultrafast - superfast - veryfast - faster - fast - medium(default preset) - slow -
    # slower - veryslow - placebo
    "r": 24,
    "crf": 21,
    "b:v": "800k",
    "ac": 1,  # Mono
    # "b:a": "128k",
    "acodec": "aac"  # copy
}

try:
    preFileStream = ffmpeg.input(PRE_FILE, **input_args)
    inputStream = ffmpeg.input(INPUT_FOLDER + INPUT_FILE_NAME, **input_args)
    a1 = preFileStream.audio
    a2 = inputStream.audio

    inputStream = ffmpeg.filter(inputStream, 'scale', size='1920x1080', force_original_aspect_ratio='decrease')
    inputStream = ffmpeg.filter(inputStream, 'pad', '1920', '1080', '(ow-iw)/2', '(oh-ih)/2')
    inputStream = ffmpeg.overlay(inputStream, OVERLAY_FILE)
    inputStream = ffmpeg.filter(inputStream, "fade", type='in', start_time=0, duration=1)
    # inputStream = ffmpeg.filter(inputStream, "fade", type='out', duration=1)

    stream = ffmpeg.concat(preFileStream, a1, inputStream, a2, v=1, a=1)

    stream = ffmpeg.output(stream, INPUT_FILE_NAME, **output_args)
    ffmpeg.run(stream)
    print(ffmpeg.compile(stream))
except FileNotFoundError as e:
    print(e.strerror)

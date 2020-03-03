<?php
define("INPUT_DIR", "../input");
?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet"
              type="text/css"/>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
        <link href="./style/style.css" rel="stylesheet" type="text/css">
        <script src="./js/moment.min.js"></script>
        <style>
            .player {
                margin: auto;
                padding: 10px;
                width: 90%;
                max-width: 900px;
                min-width: 320px;
            }

            .mediaplayer {
                position: relative;
                height: 0;
                width: 100%;
                padding-bottom: 56.25%;
                /* 16/9 */
            }

            .mediaplayer video, .mediaplayer .polyfill-video {
                position: absolute;
                top: 0;
                left: 0;
                height: 100%;
                width: 100%;
            }
        </style>
        <script>
            if (window.webshim) {
                (function () {

                    webshim.setOptions('mediaelement', {
                        replaceUI: 'auto'
                    });
                    webshim.setOptions({types: 'range'});
                    webshim.setOptions('extendNative', true);
                    webshim.polyfill('mediaelement forms forms-ext');
                })();
            }


            //add some controls
            jQuery(function ($) {
                $('div.player').each(function () {
                    var player = this;
                    var getSetCurrentTime = createGetSetHandler(
                        function () {
                            // $('div.time-slider', player).prop('values[0]', $.prop(this, 'currentTime'));
                        }, function () {
                            try {
                                $('video, audio', player).prop('currentTime', $.prop(this, 'value'));
                            } catch (er) {
                            }
                        });

                    var getSetVolume = createGetSetHandler(
                        function () {
                            $('input.volume-slider', player).prop('value', $.prop(this, 'volume'));

                        }, function () {
                            $('video, audio', player).prop('volume', $.prop(this, 'value'));
                        });

                    $('video, audio', this).bind('durationchange updateMediaState', function () {
                        var duration = $.prop(this, 'duration');
                        if (!duration) {
                            return;
                        }
                        $('div.time-slider', player).slider({
                            range: true,
                            min: 0,
                            max: duration,
                            values: [0, duration],
                            step: 0.01,
                            slide: function (event, ui) {
                                $('video, audio', player).prop('currentTime', ui.values[ui.handleIndex]);
                                $("#amount").val(moment.utc(ui.values[0] * 1000).format('HH:mm:ss.S') + " - " + moment.utc(ui.values[1] * 1000).format('HH:mm:ss.S'));
                            }
                        });
                        // $('div.time-slider', player).prop({
                        //     'max': duration,
                        //     'range': true,
                        //     'values': [ 0, duration ],
                        //     disabled: false
                        // });
                        $('span.duration', player).text(duration);

                    }).bind('progress updateMediaState', function () {
                        var buffered = $.prop(this, 'buffered');
                        if (!buffered || !buffered.length) {
                            return;
                        }
                        buffered = getActiveTimeRange(buffered, $.prop(this, 'currentTime'));
                        $('span.progress', player).text(buffered[2]);
                    }).bind('timeupdate', function () {
                        $('span.current-time', player).text($.prop(this, 'currentTime'));
                    }).bind('timeupdate', getSetCurrentTime.get
                    ).bind('emptied', function () {
                        // $('div.time-slider', player).prop('disabled', true);
                        $('span.duration', player).text('--');
                        $('span.current-time', player).text(0);
                        $('span.network-state', player).text(0);
                        $('span.ready-state', player).text(0);
                        $('span.paused-state', player).text($.prop(this, 'paused'));
                        $('span.height-width', player).text('-/-');
                        $('span.progress', player).text('0');
                    }).bind('waiting playing loadedmetadata updateMediaState', function () {
                        $('span.network-state', player).text($.prop(this, 'networkState'));
                        $('span.ready-state', player).text($.prop(this, 'readyState'));
                    }).bind('play pause', function () {
                        $('span.paused-state', player).text($.prop(this, 'paused'));
                    }).bind('volumechange', function () {
                        var muted = $.prop(this, 'muted');
                        $('span.muted-state', player).text(muted);
                        $('input.muted', player).prop('checked', muted);
                        $('span.volume', player).text($.prop(this, 'volume'));
                    }).bind('volumechange', getSetVolume.get).bind('play pause', function () {
                        $('span.paused-state', player).text($.prop(this, 'paused'));
                    }).bind('loadedmetadata updateMediaState', function () {
                        $('span.height-width', player).text($.prop(this, 'videoWidth') + '/' + $.prop(this, 'videoHeight'));
                    }).each(function () {
                        if ($.prop(this, 'readyState') > $.prop(this, 'HAVE_NOTHING')) {
                            $(this).triggerHandler('updateMediaState');
                        }
                    });

                    // $('div.time-slider', player).bind('input', getSetCurrentTime.set).prop('value[0]', 0);
                    $('input.volume-slider', player).bind('input', getSetVolume.set);

                    $('input.play', player).bind('click', function () {
                        $('video, audio', player)[0].play();
                    });
                    $('input.pause', player).bind('click', function () {
                        $('video, audio', player)[0].pause();
                    });
                    $('input.muted', player).bind('click updatemuted', function () {
                        $('video, audio', player).prop('muted', $.prop(this, 'checked'));
                    }).triggerHandler('updatemuted');
                    $('input.controls', player).bind('click', function () {
                        $('video, audio', player).prop('controls', $.prop(this, 'checked'));
                    }).prop('checked', true);

                    $('select.load-media', player).bind('change', function () {
                        var srces = $('option:selected', this).data('src');
                        if (srces) {
                            //the following code can be also replaced by the following line
                            //$('video, audio', player).loadMediaSrc(srces).play();
                            $('video, audio', player).removeAttr('src').find('source').remove().end().each(function () {
                                var mediaElement = this;
                                if (typeof srces == 'string') {
                                    srces = [srces];
                                }
                                $.each(srces, function (i, src) {

                                    if (typeof src == 'string') {
                                        src = {
                                            src: src
                                        };
                                    }
                                    $(document.createElement('source')).attr(src).appendTo(mediaElement);
                                });
                            })[0].load();
                            $('video, audio', player)[0].play();
                        }
                    }).prop('selectedIndex', 0);
                });
            });

            //helper for createing throttled get/set functions (good to create time/volume-slider, which are used as getter and setter)

            function createGetSetHandler(get, set) {
                var throttleTimer;
                var blockedTimer;
                var blocked;
                return {
                    get: function () {
                        if (blocked) {
                            return;
                        }
                        return get.apply(this, arguments);
                    },
                    set: function () {
                        clearTimeout(throttleTimer);
                        clearTimeout(blockedTimer);

                        var that = this;
                        var args = arguments;
                        blocked = true;
                        throttleTimer = setTimeout(function () {
                            set.apply(that, args);
                            blockedTimer = setTimeout(function () {
                                blocked = false;
                            }, 30);
                        }, 0);
                    }
                };
            };

            function getActiveTimeRange(range, time) {
                var len = range.length;
                var index = -1;
                var start = 0;
                var end = 0;
                for (var i = 0; i < len; i++) {
                    if (time >= (start = range.start(i)) && time <= (end = range.end(i))) {
                        index = i;
                        break;
                    }
                }
                return [index, start, end];
            }

        </script>

    </head>
    <body>
    <div class="player">
        <div class="mediaplayer">
            <video poster="http://corrupt-system.de/assets/media/sintel/sintel-trailer.jpg" controls preload="none">
                <source src="http://corrupt-system.de/assets/media/sintel/sintel-trailer.m4v" type="video/mp4"/>
                <source src="http://corrupt-system.de/assets/media/sintel/sintel-trailer.webm" type="video/webm"/>
            </video>
        </div>
        <hr/>
        <div class="container">
            <table width="100%">
                <thead>
                <th width="30%">property</th>
                <th>value/control</th>
                </thead>
                <tbody>
                <tr>
                    <th>loadMediaSrc("/input")</th>
                    <td>
                        <select class="load-media">
                            <option data-src='[{"src": "http://corrupt-system.de/assets/media/sintel/sintel-trailer.m4v", "type": "video/mp4"}]'>
                                For Test - sintel-trailer
                            </option>
                            <?php
                            foreach (getFiles() as $file) {
                                $address = constant("INPUT_DIR") . "/" . $file;
                                echo "
                        <option data-src='$address'>
                            $file
                        </option>";
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>duration</th>
                    <td><span class="duration"></span>

                    </td>
                </tr>
                <tr>
                    <th>currentTime</th>
                    <td><span class="current-time"></span>

                    </td>
                </tr>
                <tr>
                    <th>progress</th>
                    <td><span class="progress">0</span>

                    </td>
                </tr>

                <!--        <tr>-->
                <!--            <th>paused-state</th>-->
                <!--            <td><span class="paused-state">true</span>-->
                <!---->
                <!--            </td>-->
                <!--        </tr>-->
                <!--        <tr>-->
                <!--            <th>muted-state</th>-->
                <!--            <td><span class="muted-state">false</span>-->
                <!---->
                <!--            </td>-->
                <!--        </tr>-->
                <!--        <tr>-->
                <!--            <th>volume</th>-->
                <!--            <td><span class="volume">1</span>-->
                <!---->
                <!--            </td>-->
                <!--        </tr>-->
                <tr>
                    <th>videoWidth/videoHeight</th>
                    <td><span class="height-width">-/-</span>

                    </td>
                </tr>
                <!--        <tr>-->
                <!--            <th>networkState</th>-->
                <!--            <td><span class="network-state"></span>-->
                <!---->
                <!--            </td>-->
                <!--        </tr>-->
                <!--        <tr>-->
                <!--            <th>readyState</th>-->
                <!--            <td><span class="ready-state"></span>-->
                <!---->
                <!--            </td>-->
                <!--        </tr>-->
                <tr>
                    <th>currentTime</th>
                    <td>
                        <div class="time-slider" disabled></div>
                    </td>
                </tr>
                <tr>
                    <th>Range:</th>
                    <td>
                        <p>
                            <label for="amount">Trim Range:</label>
                            <input type="text" id="amount" readonly style="border:0; color:#f6931f; font-weight:bold;">
                        </p>
                    </td>
                </tr>

                <!--        <tr>-->
                <!--            <th>play</th>-->
                <!--            <td>-->
                <!--                <input value="play" type="button" class="play"/>-->
                <!--            </td>-->
                <!--        </tr>-->
                <!--        <tr>-->
                <!--            <th>pause</th>-->
                <!--            <td>-->
                <!--                <input value="pause" type="button" class="pause"/>-->
                <!--            </td>-->
                <!--        </tr>-->
                <!--        <tr>-->
                <!--            <th>muted</th>-->
                <!--            <td>-->
                <!--                <input class="muted" type="checkbox"/>-->
                <!--            </td>-->
                <!--        </tr>-->
                <!--        <tr>-->
                <!--            <th>volume</th>-->
                <!--            <td>-->
                <!--                <input class="volume-slider" type="range" value="1" max="1" step="0.01"/>-->
                <!--            </td>-->
                <!--        </tr>-->
                </tbody>
            </table>
            <div style="text-align: center;">
                <div class="form-radio">
                    <table width="100%">
                        <tr>
                            <td>
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="radio" checked="checked" value="MovieLife"/><i class="helper"></i>MovieLife
                                    </label>
                                </div>
                            </td>
                            <td>
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="radio" value="NewOldMovies"/><i class="helper"></i>NewOldMovies
                                    </label>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="form-radio">
                    <table width="100%">
                        <tr>
                            <td>
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="libx" checked="checked" value="libx264"/><i class="helper"></i>libx264
                                    </label>
                                </div>
                            </td>
                            <td>
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="libx" value="libx265"/><i class="helper"></i>libx265
                                    </label>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="button-container">
                    <button type="button" class="button" onclick="trim()"><span>Trim</span></button>
                </div>
            </div>
        </div>
    </div>

    </body>
    </html>

<script>
    function trim() {
        var fileAddress = $('div.player').find('source').attr("src");
        var trimStart = $('div.time-slider').slider("values", 0);
        var trimEnd = $('div.time-slider').slider("values", 1);
        var copyrightChannel = $('input[name=radio]:checked').val();
        var libx = $('input[name=libx]:checked').val();
        var trimURL = "doTrim.php?fileAddress=" + fileAddress + "&trimStart=" + trimStart + "&trimEnd=" + trimEnd + "&copyrightChannel=" + copyrightChannel + "&libx=" + libx;
        // window.location.href=trimURL;
        var win = window.open(trimURL, '_blank');
        win.focus();
    }
</script>
<?php
function getFiles()
{
    $dir = constant("INPUT_DIR");
    if ($dp = opendir($dir)) {
        $files = array();
        while (($file = readdir($dp)) !== false) {
            if (!is_dir($dir . $file)) {
                $files[] = $file;
            }
        }
        closedir($dp);
        return $files;
    } else {
        exit('Directory not opened.');
    }

}
?>

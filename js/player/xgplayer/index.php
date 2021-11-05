<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta name=viewport content="width=device-width,initial-scale=1,maximum-scale=1,minimum-scale=1,user-scalable=no,minimal-ui">
    <meta name="referrer" content="no-referrer">
    <title>xgplayer</title>
    <style type="text/css">
      html, body {width:100%;height:100%;margin:auto;overflow: hidden;}
    </style>
  </head>
  <body>
    <div id="mse"></div>
    <script src="index.js" charset="utf-8"></script>
      <script src="hlsindex.js" charset="utf-8"></script>
      <script>
      let player = new HlsJsPlayer({
		"id": "mse",
		"url": "<?php echo $_GET['url'];?>",
		"playsinline": true,
		"whitelist": [
				""
		],
		"poster": "1.jpg",
		"fluid": true,
		"danmu": {
				"comments": [
						{
								"duration": 15000,
								"id": "2",
								"start": 3000,
								"txt": "欢迎来到爱你播放器",
								"mode": "top"
						},
						{
								"duration": 15000,
								"id": "3",
								"start": 4000,
								"txt": "xianyui.icu",
								"mode": "bottom"
						},
						{
								"duration": 15000,
								"id": "4",
								"start": 5000,
								"txt": "西瓜播放器真心不错",
								"mode": "scroll"
						},
						{
								"duration": 15000,
								"id": "5",
								"start": 8000,
								"txt": "欢迎大家测试哈",
								"mode": "scroll"
						}
				],
				"area": {
						"start": 0,
						"end": 1
				},
				"closeDefaultBtn": false,
				"defaultOff": false,
				"panel": false
		},
		"screenShot": true,
		"download": true,
		"pip": true,
		"textTrack": [
				{
						"src": "1.vtt",
						"label": "字幕1",
						"default": true
				},
				{
						"src": "2.vtt",
						"label": "字幕2",
						"default": false
				}
		],
		"keyShortcut": "on",
		"closeVideoClick": false
      });
      
          </script>
  </body>
</html>

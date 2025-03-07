<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Player</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <style>
        body {
            position: relative;
            background-color: lightgray;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url("/assets/img/prt_Mesa.ico");
            opacity: 0.2;
            z-index: -1;
        }
    </style>
    <script>
        const videos = [
            '/storage/videos/video1.mp4',
            '/storage/videos/video2.mp4',
        ];
        let currentVideoIndex = 1;

        function togglePlayPause() {
            const video = document.getElementById('videoPlayer');
            const playButton = document.getElementById('playButton');
            if (video.paused) {
                video.play();
                playButton.textContent = 'Pause';
            } else {
                video.pause();
                playButton.textContent = 'Play';
            }
        }

        function toggleMute() {
            const video = document.getElementById('videoPlayer');
            const muteButton = document.getElementById('muteButton');
            video.muted = !video.muted;
            muteButton.textContent = video.muted ? 'Unmute' : 'Mute';
        }

        function fullScreenVideo() {
            const video = document.getElementById('videoPlayer');
            if (video.requestFullscreen) {
                video.requestFullscreen();
            }
        }

        function next() {
            currentVideoIndex++;
            if (currentVideoIndex >= videos.length) {
                currentVideoIndex = 0; // Loop to the first video
            }
            updateVideoSource();
        }

        function previous() {
            currentVideoIndex--;
            if (currentVideoIndex < 0) {
                currentVideoIndex = videos.length - 1; // Loop to the last video
            }
            updateVideoSource();
        }

        function updateVideoSource() {
            const videoPlayer = document.getElementById('videoPlayer');
            videoPlayer.src = videos[currentVideoIndex];
            videoPlayer.play(); // Optionally, autoplay the new video
        }
    </script>
</head>

<body class="d-flex align-items-center justify-content-center min-vh-100">
    <div class="bg-white p-5 rounded shadow-lg w-50">
        <h1 class="text-2xl font-weight-bold mb-4 text-center">Reproductor PRT</h1>
        <video id="videoPlayer" class="w-100 rounded mb-4" controls autoplay loop muted>
            <source src="/storage/videos/video2.mp4" type="video/mp4">
            Your browser does not support the video tag.
        </video>
        <div class="d-flex justify-content-center">
            <button id="previous" onclick="previous()" class="bi bi-arrow-left-short  btn btn-warning mx-2"> </button>
            <button id="playButton" onclick="togglePlayPause()" class="btn btn-info mx-2">Play</button>
            <button id="muteButton" onclick="toggleMute()" class="btn btn-info mx-2">Mute</button>
            <button onclick="fullScreenVideo()" class="btn btn-info mx-2">Fullscreen</button>
            <button id="next" onclick="next()" class="bi bi-arrow-right-short  btn btn-warning mx-2"></button>
        </div>
    </div>

</body>

</html>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Reproductor PRT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <style>
        body {
            position: relative;
            background-color: lightgray;
        }

        body::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: url("/assets/img/prt_Mesa.ico");
            opacity: 0.2;
            z-index: -1;
        }

        .overlay-button {
            position: fixed;
            top: 5%;
            right: 3%;
            font-size: 1.2vw;
            padding: 1em;
            z-index: 1000;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .overlay-button:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        #sortable-list .list-group-item:active {
            cursor: grabbing;
            /* Change cursor to 'grabbing' when actively dragging */
        }

        .sortable-ghost {
            opacity: 0.4;
            /* Highlight dragged item */
        }

        .sortable-chosen {
            background-color: #dcdcdc;
            /* Add background color for chosen item */
        }

        .sortable-drag {
            opacity: 0.7;
            /* Add opacity effect while dragging */
        }
    </style>
</head>

<body class="d-flex align-items-center justify-content-center min-vh-100">
    <div class="bg-white p-5 rounded shadow-lg w-50">
        <video id="videoPlayer" class="w-100 rounded mb-4" controls autoplay muted preload="auto">
            <source src="/storage/videos/video2.mp4" type="video/mp4">
            Your browser does not support the video tag.
        </video>
        <div class="d-flex justify-content-center">
            <button id="previous" onclick="previous()" class="bi bi-arrow-left-short  btn btn-warning mx-2"> </button>
            <button id="playButton" onclick="togglePlayPause()" class="bi btn btn-info mx-2"></button>
            <button id="muteButton" onclick="toggleMute()" class="bi btn btn-info mx-2"></button>
            <button onclick="fullScreenVideo()" class="bi bi-arrows-fullscreen btn btn-info mx-2"></button>
            <button id="next" onclick="next()" class="bi bi-arrow-right-short  btn btn-warning mx-2"></button>
        </div>
    </div>

    <button class="overlay-button bi bi-gear btn btn-primary" id="openAdmin">
        Administrar
    </button>

    <!-- Modal -->
    <div class="modal fade" id="adminModal" tabindex="-1" role="dialog" aria-labelledby="adminModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="adminModalTitle">Administrador de Videos</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="section">
                        <h5>Agregar Video</h5>
                        <div class="form-group">
                            <form action="{{ route('video.store') }}" method="POST" enctype="multipart/form-data" id="save-form">
                                @csrf
                                <label for="video-name" class="col-form-label">Nombre:</label>
                                <input type="text" class="form-control" id="video-name" name="name">
                        </div>
                        <div class="form-group">
                            <label for="video-input" class="col-form-label">Video:</label>
                            <input type="file" class="form-control" id="video-input" name="video">
                        </div>
                        <div class="d-flex justify-content-end mt-3">
                            <button type="button" class="btn btn-primary" id="save-video">Guardar</button>
                            <button type="button" class="btn btn-secondary ms-2" data-bs-dismiss="modal">Cerrar</button>
                        </div>
                        </form>
                    </div>

                    <hr>

                    <div class="section">
                        <h5>Orden</h5>
                        <div class="d-flex justify-content-start mt-3 mb-3">
                            <div class="form-check form-switch me-3">
                                <input type="checkbox" class="form-check-input" id="autoplay-check">
                                <label for="autoplay-check" class="form-check-label">Autoplay</label>
                            </div>

                            <div class="form-check form-switch me-3">
                                <input type="checkbox" class="form-check-input" id="loop-check">
                                <label for="loop-check" class="form-check-label">Loop</label>
                            </div>

                            <div class="form-check form-switch me-3">
                                <input type="checkbox" class="form-check-input" id="auto-next-check">
                                <label for="auto-next-check" class="form-check-label">Reproducir Siguiente</label>
                            </div>
                        </div>

                        <div class="list">
                            <ul id="sortable-list" class="list-group">
                                <li class="list-group-item d-flex justify-content-between align-items-center" data-id="1">
                                    <span>Video 1</span>
                                    <div class="form-check form-switch ms-auto">
                                        <input type="checkbox" class="form-check-input" id="status">
                                    </div>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center" data-id="2">
                                    <span>Video 2</span>
                                    <div class="form-check form-switch ms-auto">
                                        <input type="checkbox" class="form-check-input" id="checkbox2">
                                    </div>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center" data-id="3">
                                    <span>Video 3</span>
                                    <div class="form-check form-switch ms-auto">
                                        <input type="checkbox" class="form-check-input" id="checkbox3">
                                    </div>
                                </li>
                            </ul>
                        </div>

                        <div class="d-flex justify-content-end mt-3">
                            <button type="button" class="btn btn-primary" id="update-video">Guardar</button>
                            <button type="button" class="btn btn-secondary ms-2" data-bs-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


</body>

<script>
    const videos = [
        '/storage/videos/video1.mp4',
        '/storage/videos/video2.mp4',
    ];
    let currentVideoIndex = 1;

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const adminModal = new bootstrap.Modal(document.getElementById('adminModal'));
    const videoPlayer = document.getElementById('videoPlayer');
    const muteButton = document.getElementById('muteButton');
    const playButton = document.getElementById('playButton');
    const openAdmin = document.getElementById('openAdmin');
    const saveButton = document.getElementById('save-video');
    const updateButton = document.getElementById('update-video');
    const createVideo = document.getElementById('save-form');


    if (saveButton) {
        saveButton.addEventListener('click', async function() {
            event.preventDefault();
            saveButton.disabled = true;

            try {
                const formData = new FormData(createVideo);

                const response = await fetch('/guardar-video', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: formData
                });

                if (!response.ok) {
                    // Handle error response
                    const text = await response.text();
                    console.error('Error response:', text);
                    return;
                }

                const result = await response.json(); // Only parse if it's JSON
                console.log('Item created successfully:', result);
                alert('Video saved successfully!');
                adminModal.hide();
                return;
            } catch (error) {
                console.error(error.message);
                alert('An error occurred while creating the item.');
                saveButton.disabled = false;

            } finally {
                // Re-enable the submit button
                saveButton.disabled = false;
                return;
            }
        });

    }

    videoPlayer.addEventListener('ended', next);

    document.addEventListener('DOMContentLoaded', function() {
        if (videoPlayer.muted) {
            muteButton.classList.add('bi-volume-mute-fill');
        } else {
            muteButton.classList.add('bi-volume-up-fill');
        }

        if (videoPlayer.paused) {
            playButton.classList.add('bi-pause-fill');
        } else {
            playButton.classList.add('bi-play-fill')
        }

    });

    document.addEventListener('keydown', function(event) {
        if (event.key === 'ArrowRight') {
            next();
        } else if (event.key === 'ArrowLeft') {
            previous();
        }
    });

    openAdmin.addEventListener('click', function() {
        createVideo.reset();
        adminModal.show()
        
    });

    function togglePlayPause() {
        if (videoPlayer.paused) {
            videoPlayer.play();
            playButton.classList.remove('bi-play-fill');
            playButton.classList.add('bi-pause-fill');

        } else {
            videoPlayer.pause();
            playButton.classList.remove('bi-pause-fill');
            playButton.classList.add('bi-play-fill');
        }
    }

    function toggleMute() {

        if (videoPlayer.muted) {
            videoPlayer.muted = false;
            muteButton.classList.remove('bi-volume-mute-fill');
            muteButton.classList.add('bi-volume-up-fill');
        } else {
            videoPlayer.muted = true;
            muteButton.classList.remove('bi-volume-up-fill');
            muteButton.classList.add('bi-volume-mute-fill')
        }
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
        videoPlayer.src = videos[currentVideoIndex];
        videoPlayer.play();
    }

    new Sortable(document.getElementById('sortable-list'), {
        animation: 150,
        ghostClass: 'sortable-ghost',
        chosenClass: 'sortable-chosen',
    });
</script>

</html>
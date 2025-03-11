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
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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

        .disabled-row {
            background-color: #f0f0f0 !important;
            /* Light gray */
            color: #999 !important;
            /* Dim text */
            pointer-events: none;
            /* Disable clicks */
            opacity: 0.6;
            /* Make it look inactive */
        }

        .disabled-row .toggle-status {
            pointer-events: auto;
            /* Allow interaction with the toggle */
            opacity: 1;
            /* Make sure the toggle is fully visible */
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
                            <form action="{{ route('update.config') }}" method="POST" enctype="multipart/form-data" id="update-form">
                                @csrf
                                @method('PUT')
                                <div class="d-flex align-items-center">
                                    <div class="form-check form-switch me-3">
                                        <input type="checkbox" class="form-check-input " id="autoplay-check" name="autoplay">
                                        <label for="autoplay-check" class="form-check-label">Autoplay</label>
                                    </div>

                                    <div class="form-check form-switch me-3">
                                        <input type="checkbox" class="form-check-input" id="loop-check" name="loop">
                                        <label for="loop-check" class="form-check-label">Loop</label>
                                    </div>

                                    <div class="form-check form-switch me-3">
                                        <input type="checkbox" class="form-check-input" id="auto-next-check" name="auto_next">
                                        <label for="auto-next-check" class="form-check-label">Reproducir Siguiente</label>
                                    </div>
                                </div>

                            </form>
                        </div>

                        <div class="list">
                            <ul id="sortable-list" class="list-group">
                                @foreach($videos as $video)
                                <li class="{{ $video->status == 0 ? 'disabled-row' : '' }}" data-video-id="${video.id}">
                                    <span>{{ $video->name }}</span>
                                    <div class="form-check form-switch ms-auto">
                                        <input type="checkbox" class="form-check-input toggle-status" data-video-id="${video.id}"
                                            data-status="${video.id}" {{ $video->status ? 'checked' : '' }}>
                                    </div>
                                </li>
                                @endforeach
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


    <!-- Upload progress bar container (initially hidden) -->
    <div id="upload-progress-container" style="  display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 50%; z-index: 1100;">
        <div class="progress">
            <div id="upload-progress-bar" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                0%
            </div>
        </div>
        <p>Uploading video...</p>
    </div>


</body>

<script>
    let videos = [];
    let videosEnabled = []
    let currentVideoIndex = 0;
    let config = {};
    let originalConfig = {};

    // Fetch videos from your API endpoint and update the DOM list
    async function loadVideos() {
        try {
            const response = await fetch('/videos'); // your endpoint that returns videos as JSON
            if (!response.ok) {
                throw new Error('Failed to fetch videos');
            }
            const data = await response.json();

            videos = data.filter(video => video.status);
            videosEnabled = data.filter(video => video.status === 1);


            // Sort the videos by the order property
            videos = data.sort((a, b) => a.order - b.order);
            videosEnabled = videosEnabled.sort((a, b) => a.order - b.order);
            // Set the first video as the current video if available
            if (videosEnabled.length > 0) {
                currentVideoIndex = 0;
                updateVideoSource();
            }

            const sortableList = document.getElementById('sortable-list');
            sortableList.innerHTML = ''; // Clear the list first

            // Create two arrays: one for active videos, one for disabled ones
            const activeVideos = [];
            const disabledVideos = [];

            videos.forEach(video => {
                const li = document.createElement('li');
                li.className = 'list-group-item d-flex justify-content-between align-items-center';
                li.setAttribute('data-video-id', video.id);

                // Apply the checkbox checked state
                const isChecked = video.status ? 'checked' : '';

                // Add the disabled style class if the video is inactive
                if (video.status === 0) {
                    li.classList.add('disabled-row'); // Add disabled class for styling
                    disabledVideos.push(li); // Store disabled video
                } else {
                    activeVideos.push(li); // Store active video
                }

                li.innerHTML = `
                <span>${video.name}</span>
                <div class="form-check form-switch ms-auto">
                    <input 
                        type="checkbox" 
                        class="form-check-input toggle-status" 
                        data-video-id="${video.id}"
                        ${isChecked}>
                </div>
            `;
            });

            // Append active videos first, then disabled ones to the list
            activeVideos.forEach(li => sortableList.appendChild(li));
            disabledVideos.forEach(li => sortableList.appendChild(li));

        } catch (error) {
            console.error('Error loading videos:', error);
        }
    }

    async function fetchConfig() {
        try {
            const response = await fetch('/config');
            if (!response.ok) {
                throw new Error('Failed to fetch config');
            }
            const config = await response.json();
            originalConfig = {
                ...config
            };
            return config;
        } catch (error) {
            console.error(error.message);
            return {}; // Return an empty object in case of an error
        }
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const adminModal = new bootstrap.Modal(document.getElementById('adminModal'));
    const videoPlayer = document.getElementById('videoPlayer');
    const muteButton = document.getElementById('muteButton');
    const playButton = document.getElementById('playButton');
    const openAdmin = document.getElementById('openAdmin');
    const saveButton = document.getElementById('save-video');
    const updateButton = document.getElementById('update-video');
    const createVideo = document.getElementById('save-form');
    const updateConfig = document.getElementById('update-form');
    const nameField = document.getElementById('video-name');
    const videoInput = document.getElementById('video-input');
    const progressContainer = document.getElementById('upload-progress-container');
    const progressBar = document.getElementById('upload-progress-bar');


    if (saveButton) {
        saveButton.addEventListener('click', async function(event) {
            event.preventDefault();
            saveButton.disabled = true;
            updateButton.disabled = true;

            try {
                if (!nameField.value.trim() || !videoInput.files.length) {
                    alert('Ingrese un nombre y cargue un video antes de guardar.');
                    saveButton.disabled = false;
                    updateButton.disabled = false;
                    return;
                }

                if (progressContainer) {
                    progressContainer.style.display = 'block';
                }

                const formData = new FormData(createVideo);

                const response = await axios.post('/guardar-video', formData, {
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'multipart/form-data',
                    },
                    onUploadProgress: function(progressEvent) {
                        const percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                        if (progressBar) {
                            progressBar.style.width = percentCompleted + '%';
                            progressBar.textContent = percentCompleted + '%';
                        }
                        // Ensure it reaches the full width visually
                        if (percentCompleted === 100) {
                            progressBar.style.transition = "none";
                            progressBar.offsetWidth; // Trigger a reflow
                            progressBar.style.transition = "width 0.3s ease";
                            progressBar.style.width = "100%";
                        }
                    }

                });

                console.log('Item created successfully:', response.data);
                alert('Video saved successfully!');
                adminModal.hide();
                window.location.reload();
            } catch (error) {
                console.error(error.message);
                alert('An error occurred while creating the item.');
            } finally {
                if (progressContainer) {
                    progressContainer.style.display = 'none';
                }
                saveButton.disabled = false;
                updateButton.disabled = false;
            }
        });
    }


    if (updateButton) {
        updateButton.addEventListener('click', async function() {
            event.preventDefault();
            updateButton.disabled = true;
            saveButton.disabled = true;
            try {
                const formData = new FormData(updateConfig);
                formData.set('autoplay', document.getElementById('autoplay-check').checked ? '1' : '0');
                formData.set('loop', document.getElementById('loop-check').checked ? '1' : '0');
                formData.set('auto_next', document.getElementById('auto-next-check').checked ? '1' : '0');
                const response = await fetch('/actualizar-config', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: formData
                });

                if (!response.ok) {
                    const text = await response.text();
                    console.error('Error response:', text);
                    return;
                }

                const result = await response.json(); // Only parse if it's JSON
                console.log('Item created successfully:', result);
                alert('Video config updated successfully!');
                adminModal.hide();
                window.location.reload();
                return;
            } catch (error) {
                console.error(error.message);
                alert('An error occurred while updating the config.');
                updateButton.disabled = false;
                saveButton.disabled = false;
                return;

            } finally {
                updateButton.disabled = false;
                saveButton.disabled = false;
                return;
            }
        });

    }

    if (videoPlayer) {
        // When the video is paused
        videoPlayer.addEventListener("pause", function () {
            playButton.classList.remove('bi-pause-fill');
            playButton.classList.add('bi-play-fill');
        });

        // When the video is resumed (played)
        videoPlayer.addEventListener("play", function () {
            playButton.classList.remove('bi-play-fill');
            playButton.classList.add('bi-pause-fill');
        });

        videoPlayer.addEventListener("volumechange", function () {
            if (videoPlayer.muted) {
                muteButton.classList.remove('bi-volume-up-fill');
                muteButton.classList.add('bi-volume-mute-fill');
            } else {
                muteButton.classList.remove('bi-volume-mute-fill');
                muteButton.classList.add('bi-volume-up-fill');
            }
    });
    }
    document.addEventListener('DOMContentLoaded', async function() {
        await loadVideos();

        if (videoPlayer.muted) {
            muteButton.classList.add('bi-volume-mute-fill');
        } else {
            muteButton.classList.add('bi-volume-up-fill');
        }
        try {
            config = await fetchConfig();

            if (config.autoplay) {
                playButton.classList.add('bi-pause-fill');
            } else {
                playButton.classList.add('bi-play-fill');
            }

            if (config.autoplay !== undefined) {
                videoPlayer.autoplay = config.autoplay;
                document.getElementById('autoplay-check').checked = config.autoplay;
            }

            if (config.loop !== undefined) {
                videoPlayer.loop = config.loop;
                document.getElementById('loop-check').checked = config.loop;
            }

            if (config.auto_next !== undefined) {
                videoPlayer.auto_next = config.auto_next;
                document.getElementById('auto-next-check').checked = config.auto_next;
            }
        } catch (error) {
            console.error('Failed to fetch config:', error);
        }
    });


    document.addEventListener('keydown', function(event) {
        if (event.key === 'ArrowRight') {
            next();
        } else if (event.key === 'ArrowLeft') {
            previous();
        }
    });

    function applyConfig() {
        document.getElementById('autoplay-check').checked = originalConfig.autoplay || false;
        document.getElementById('loop-check').checked = originalConfig.loop || false;
        document.getElementById('auto-next-check').checked = originalConfig.auto_next || false;
    }
    openAdmin.addEventListener('click', function() {
        createVideo.reset();
        applyConfig();
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
        if (currentVideoIndex >= videosEnabled.length) {
            currentVideoIndex = 0; // Loop to the first video
        }
        updateVideoSource();
    }

    function previous() {
        currentVideoIndex--;
        if (currentVideoIndex < 0) {
            currentVideoIndex = videosEnabled.length - 1; // Loop to the last video
        }
        updateVideoSource();
    }

    function updateVideoSource() {
        if (videosEnabled.length === 0) {
            console.error('No videos available to play.');
            return;
        }
        if (currentVideoIndex < 0 || currentVideoIndex >= videosEnabled.length) {
            console.error('Invalid video index:', currentVideoIndex);
            currentVideoIndex = 0; // Reset to the first video
        }
        videoPlayer.src = videosEnabled[currentVideoIndex]?.path ?? ''; // Use optional chaining and fallback

    }


    new Sortable(document.getElementById('sortable-list'), {
        animation: 150,
        ghostClass: 'sortable-ghost',
        chosenClass: 'sortable-chosen',
        onEnd: function(evt) {
            const videoOrder = [];
            const videoItems = evt.from.querySelectorAll('.list-group-item');

            videoItems.forEach((item, index) => {
                const videoId = item.getAttribute('data-id');
                videoOrder.push({
                    id: videoId,
                    order: index + 1, // 1-based index for ordering
                });
            });

            // Send updated order to the backend
            updateVideoOrder(videoOrder);
        }
    });

    document.getElementById('autoplay-check').addEventListener('change', function(event) {
        const isChecked = event.target.checked;
        config.autoplay = event.target.checked;

        if (isChecked) {
            videoPlayer.autoplay = isChecked;
        }
    });

    // Loop checkbox handler
    document.getElementById('loop-check').addEventListener('change', function(event) {
        const isChecked = event.target.checked;
        config.loop = isChecked;

        const autoNextCheck = document.getElementById('auto-next-check');

        if (isChecked) {
            // Disable 'auto-next' temporarily and uncheck it
            autoNextCheck.disabled = true;
            autoNextCheck.checked = false;
            config.auto_next = false;
            videoPlayer.auto_next = false;

            videoPlayer.loop = isChecked;
            // Re-enable after a short delay (cooldown)
            setTimeout(() => {
                autoNextCheck.disabled = false;
            }, 1000); // 1-second cooldown
        }

    });

    // Auto-next checkbox handler
    document.getElementById('auto-next-check').addEventListener('change', function(event) {
        const isChecked = event.target.checked;
        config.auto_next = isChecked;
        videoPlayer.auto_next = isChecked;

        const loopCheck = document.getElementById('loop-check');

        if (isChecked) {
            // Disable 'loop' temporarily and uncheck it
            loopCheck.disabled = true;
            loopCheck.checked = false;
            config.loop = false;
            videoPlayer.loop = false;
            // Re-enable after a short delay (cooldown)
            setTimeout(() => {
                loopCheck.disabled = false;
            }, 1000); // 1-second cooldown
        }
    });
    videoPlayer.addEventListener('ended', function() {
        if (config.auto_next) {
            next();
        }
    });


    $(document).on('change', '.toggle-status', async function(event) {
        const toggle = $(event.target); // directly get the target element as a jQuery object
        const videoId = toggle.data('video-id'); // get the video ID using data-video-id
        const status = toggle.prop('checked') ? 1 : 0; // get the checked status (1 or 0)

        const confirmClose = confirm('Are you sure you want to perform this action?');
        if (!confirmClose) {
        // 🔄 Revert the checkbox state
        toggle.prop('checked', !toggle.prop('checked'));
        return;
    }

        try {
            // Update status in the backend
            const response = await fetch(`/update-status/${videoId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    status
                }),
            });

            if (!response.ok) {
                throw new Error('Failed to update status');
            }

            const result = await response.json();
            console.log(result.message);

            // Update the UI based on the new status
            const row = toggle.closest('li'); // find the closest li element for this checkbox

            if (status === 0) {
                row.addClass('disabled-row'); // Add the disabled-row class to the list item
                // Move the disabled row to the bottom of the list
                row.detach();
                $('#sortable-list').append(row); // Append the row to the end
            } else {
                row.removeClass('disabled-row'); // Remove the disabled-row class if it is enabled
                // Move the enabled row back to its original position if necessary
                row.detach();
                $('#sortable-list').prepend(row); // Prepend it back to the top (or use append for order)
            }

            // Recalculate the order and send it to the backend
            updateVideoOrder();

        } catch (error) {
            console.error("Error updating status:", error);
            alert("Failed to update status. Please try again.");
        }
    });

    // Function to recalculate the order of the videos
    function updateVideoOrder() {
        const videoOrder = [];
        const videoItems = $('#sortable-list').children('.list-group-item');

        videoItems.each((index, item) => {
            const videoId = $(item).data('video-id');
            videoOrder.push({
                id: videoId,
                order: index + 1, // 1-based index for ordering
            });
        });

        // Send updated order to the backend
        sendVideoOrderToServer(videoOrder);
    }

    // Function to send the order to the backend
    async function sendVideoOrderToServer(videoOrder) {
        try {
            const response = await fetch('/update-video-order', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    order: videoOrder
                })
            });

            if (response.ok) {
                console.log('Video order updated successfully');
            } else {
                console.error('Failed to update video order');
            }
        } catch (error) {
            console.error('Error while updating video order:', error);
        }
    }
</script>

</html>
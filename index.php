<!DOCTYPE html>
<html lang="zh-CN" data-theme="fantasy">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Podripple</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@5" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/style.css">
</head>

<body class="bg-base-100">

    <div class="container mx-auto min-h-screen py-4 grid grid-cols-[180px_4fr_2fr] gap-4">
        <header>
            <div>
                <h1 class="flex items-center gap-2">
                    <i data-lucide="podcast"></i>
                    <span class="text-2xl font-bold">PodRipple</span>
                </h1>
            </div>
            <nav class="mt-8">
                <ul class="grid grid-flow-row gap-2">
                    <li class="indent-8"><a href="#">Home</a></li>
                    <li class="indent-8"><a href="#">Podcasts</a></li>
                    <li class="indent-8"><a href="#">Blog</a></li>
                    <li class="indent-8"><a href="#">About</a></li>
                    <li class="indent-8"><a href="#">Contact</a></li>
                </ul>
            </nav>
        </header>
        <main>
            <div class="carousel w-full h-64 rounded-xl shadow-xl">
                <div id="slide2" class="carousel-item relative w-full">
                    <img
                        src="https://images.unsplash.com/photo-1519389950473-47ba0277781c?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80"
                        class="w-full h-full object-cover" />
                    <div class="absolute left-5 right-5 top-1/2 flex -translate-y-1/2 transform justify-between">
                        <a href="#slide1" class="btn btn-circle">❮</a>
                        <a href="#slide3" class="btn btn-circle">❯</a>
                    </div>
                </div>
                <div id="slide1" class="carousel-item relative w-full">
                    <img
                        src="https://cdn.pixabay.com/photo/2024/05/28/12/28/ship-8793759_640.jpg"
                        class="w-full h-full object-cover" />
                    <div class="absolute left-5 right-5 top-1/2 flex -translate-y-1/2 transform justify-between">
                        <a href="#slide4" class="btn btn-circle">❮</a>
                        <a href="#slide2" class="btn btn-circle">❯</a>
                    </div>
                </div>
            </div>
            <div class="mt-4">
                <div class="flex justify-between items-center">
                    <h2 class="text-xl font-bold">PODCASTS</h2>
                    <a href="#" class="text-xs link">See all</a>
                </div>
                <ul class="flex gap-2 mt-4">
                    <li>
                        <button class="btn bg-base-300 rounded-full btn-sm">Recent</button>
                    </li>
                    <li>
                        <button class="btn bg-base-100 rounded-full btn-sm">Popular</button>
                    </li>
                    <li>
                        <button class="btn bg-base-100 rounded-full btn-sm">Random</button>
                    </li>
                </ul>
                <ul class="grid grid-flow-row gap-y-4 mt-4">
                    <li>
                        <div class="bg-base-200 shadow-sm">
                            <div class="p-4 grid grid-cols-[60px_1fr_60px] items-center">
                                <div>
                                    <a href="#">
                                        <img src="https://images.unsplash.com/photo-1478737270239-2f02b77fc618?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=100&q=80" alt="Podcast 1" class="w-10 h-10 rounded-md" />
                                    </a>
                                </div>
                                <div class="grid grid-flow-row gap-1">
                                    <h4 class="text-md font-bold">Podcast 1</h4>
                                    <p class="text-xs text-base-content/70">
                                        <span>2025/08/20 10:00</span>
                                        <span>|</span>
                                        <span>100k views</span>
                                    </p>
                                </div>
                                <div>
                                    <button class="btn btn-circle btn-sm btn-ghost btn-primary ">
                                        <i data-lucide="play" class="text-sm"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </li>
                    <li>
                        <div class="bg-base-200 shadow-sm">
                            <div class="p-4 grid grid-cols-[60px_1fr_60px] items-center">
                                <div>
                                    <a href="#">
                                        <img src="https://images.unsplash.com/photo-1478737270239-2f02b77fc618?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=100&q=80" alt="Podcast 1" class="w-10 h-10 rounded-md" />
                                    </a>
                                </div>
                                <div>
                                    <h4 class="text-md font-bold">Podcast 1</h4>
                                    <p class="text-xs text-base-content/70">
                                        <span>2025/08/20 10:00</span>
                                        <span>|</span>
                                        <span>100k views</span>
                                    </p>
                                </div>
                                <div>
                                    <button class="btn btn-circle btn-sm btn-ghost btn-primary">
                                        <i data-lucide="play" class="text-sm"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </li>
                    <li>
                        <div class="bg-base-200 shadow-sm">
                            <div class="p-4 grid grid-cols-[60px_1fr_60px] items-center">
                                <div>
                                    <a href="#">
                                        <img src="https://images.unsplash.com/photo-1478737270239-2f02b77fc618?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=100&q=80" alt="Podcast 1" class="w-10 h-10 rounded-md" />
                                    </a>
                                </div>
                                <div>
                                    <h4 class="text-md font-bold">Podcast 1</h4>
                                    <p class="text-xs text-base-content/70">
                                        <span>2025/08/20 10:00</span>
                                        <span>|</span>
                                        <span>100k views</span>
                                    </p>
                                </div>
                                <div>
                                    <button class="btn btn-circle btn-sm btn-ghost btn-primary">
                                        <i data-lucide="play" class="text-sm"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>

            <div class="mt-4">
                <div class="flex justify-between items-center">
                    <h2 class="text-xl font-bold">BLOG</h2>
                    <a href="#" class="text-xs link">See all</a>
                </div>
                <ul class="grid grid-cols-4 gap-4 gap-y-8 mt-4">
                    <li>
                        <div class="aspect-square overflow-hidden rounded-lg">
                            <a href="#" class="block h-full">
                                <img src="https://images.unsplash.com/photo-1461749280684-dccba630e2f6?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=400&q=80" class="w-full h-full object-cover" />
                            </a>
                        </div>
                        <div class="mt-2">
                            <h4 class="text-md font-bold">Post 1</h4>
                            <p class="text-xs text-base-content/70">
                                <a href="#" class="link">Company</a>
                                <span>|</span>
                                <span>7 22, 2025</span>
                            </p>
                        </div>
                    </li>
                    <li>
                        <div class="aspect-square overflow-hidden rounded-lg">
                            <a href="#" class="block h-full">
                                <img src="https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=400&q=80" class="w-full h-full object-cover" />
                            </a>
                        </div>
                        <div class="mt-2">
                            <h4 class="text-md font-bold">Post 2</h4>
                            <p class="text-xs text-base-content/70">
                                <a href="#" class="link">Company</a>
                                <span>|</span>
                                <span>7 22, 2025</span>
                            </p>
                        </div>
                    </li>
                    <li>
                        <div class="aspect-square overflow-hidden rounded-lg">
                            <a href="#" class="block h-full">
                                <img src="https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=400&q=80" class="w-full h-full object-cover" />
                            </a>
                        </div>
                        <div class="mt-2">
                            <h4 class="text-md font-bold">Post 3</h4>
                            <p class="text-xs text-base-content/70">
                                <a href="#" class="link">Company</a>
                                <span>|</span>
                                <span>7 22, 2025</span>
                            </p>
                        </div>
                    </li>
                    <li>
                        <div class="aspect-square overflow-hidden rounded-lg">
                            <a href="#" class="block h-full">
                                <img src="https://images.unsplash.com/photo-1525547719571-a2d4ac8945e2?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=400&q=80" class="w-full h-full object-cover" />
                            </a>
                        </div>
                        <div class="mt-2">
                            <h4 class="text-md font-bold">Post 4</h4>
                            <p class="text-xs text-base-content/70">
                                <a href="#" class="link">Company</a>
                                <span>|</span>
                                <span>7 22, 2025</span>
                            </p>
                        </div>
                    </li>
                    <li>
                        <div class="aspect-square overflow-hidden rounded-lg">
                            <a href="#" class="block h-full">
                                <img src="https://images.unsplash.com/photo-1488590528505-98d2b5aba04b?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=400&q=80" class="w-full h-full object-cover" />
                            </a>
                        </div>
                        <div class="mt-2">
                            <h4 class="text-md font-bold">Post 5</h4>
                            <p class="text-xs text-base-content/70">
                                <a href="#" class="link">Company</a>
                                <span>|</span>
                                <span>7 22, 2025</span>
                            </p>
                        </div>
                    </li>
                    <li>
                        <div class="aspect-square overflow-hidden rounded-lg">
                            <a href="#" class="block h-full">
                                <img src="https://images.unsplash.com/photo-1516321318423-f06f85e504b3?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=400&q=80" class="w-full h-full object-cover" />
                            </a>
                        </div>
                        <div class="mt-2">
                            <h4 class="text-md font-bold">Post 6</h4>
                            <p class="text-xs text-base-content/70">
                                <a href="#" class="link">Company</a>
                                <span>|</span>
                                <span>7 22, 2025</span>
                            </p>
                        </div>
                    </li>
                    <li>
                        <div class="aspect-square overflow-hidden rounded-lg">
                            <a href="#" class="block h-full">
                                <img src="https://images.unsplash.com/photo-1486312338219-ce68d2c6f44d?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=400&q=80" class="w-full h-full object-cover" />
                            </a>
                        </div>
                        <div class="mt-2">
                            <h4 class="text-md font-bold">Post 7</h4>
                            <p class="text-xs text-base-content/70">
                                <a href="#" class="link">Company</a>
                                <span>|</span>
                                <span>7 22, 2025</span>
                            </p>
                        </div>
                    </li>
                    <li>
                        <div class="aspect-square overflow-hidden rounded-lg">
                            <a href="#" class="block h-full">
                                <img src="https://images.unsplash.com/photo-1504384308090-c894fdcc538d?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=400&q=80" class="w-full h-full object-cover" />
                            </a>
                        </div>
                        <div class="mt-2">
                            <h4 class="text-md font-bold">Post 8</h4>
                            <p class="text-xs text-base-content/70">
                                <a href="#" class="link">Company</a>
                                <span>|</span>
                                <span>7 22, 2025</span>
                            </p>
                        </div>
                    </li>
                </ul>
            </div>

        </main>
        <aside>
            <label class="input rounded-xl w-full">
                <svg class="h-[1em] opacity-50" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <g
                        stroke-linejoin="round"
                        stroke-linecap="round"
                        stroke-width="2.5"
                        fill="none"
                        stroke="currentColor">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.3-4.3"></path>
                    </g>
                </svg>
                <input type="search" class="grow " placeholder="Search" />
                <kbd class="kbd kbd-sm">⌘</kbd>
                <kbd class="kbd kbd-sm">K</kbd>
            </label>
            <div class="card bg-secondary text-secondary-content w-full mt-4">
                <div class="card-body">
                    <div class="grid grid-cols-[80px_1fr] gap-4 items-center bg-primary/10 p-4 rounded-lg">
                        <div>
                            <img src="https://images.unsplash.com/photo-1478737270239-2f02b77fc618?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=100&q=80" alt="Podcast 1" class="w-20 h-20 rounded-md" />
                        </div>
                        <div>
                            <h4 class="text-md font-bold">Podcast 1</h4>
                            <p class="text-xs text-base-content/70">
                                <span>2025/08/20 10:00</span>
                                <span>|</span>
                                <span>100k views</span>
                            </p>
                        </div>
                    </div>
                    <div class="mt-4" id="wave">
                        <canvas id="waveCanvas" class="w-full" height="60"></canvas>
                    </div>
                    <div class="mt-0 w-full">
                        <div class="grid grid-cols-[1fr_1fr]">
                            <span>0:00</span>
                            <span class="justify-self-end" id="sound-duration">0:00</span>
                        </div>
                        <input type="range" min="0" max="100" value="0" id="sound-progress" class="range range-xs range-success w-full" oninput="seek(this.value)" />
                    </div>
                    <div class="mt-4 grid grid-cols-[1fr_1fr_1fr] gap-4 items-center w-full">
                        <div>
                            <i data-lucide="list-music" class="cursor-pointer w-4 h-4"></i>
                        </div>
                        <div class="flex justify-center gap-4 items-center">
                            <i data-lucide="skip-back" class="cursor-pointer w-4 h-4"></i>
                            <i data-lucide="play" class="cursor-pointer w-6 h-6 bg-success-500 rounded-full" data-type="play" id="play-pause-button" onclick="playOrPause()"></i>
                            <i data-lucide="skip-forward" class="cursor-pointer w-4 h-4"></i>
                        </div>
                        <div class="justify-self-end">
                            <i data-lucide="volume" class="cursor-pointer w-4 h-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </aside>

    </div>
    <footer class="mt-4 text-center mx-auto border-t-1 border-dotted border-base-content/10 p-2 text-base-content/70 text-xs">
        © 2025 - PodRipple copyright
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/howler/2.2.4/howler.min.js" integrity="sha512-xi/RZRIF/S0hJ+yJJYuZ5yk6/8pCiRlEXZzoguSMl+vk2i3m6UjUO/WcZ11blRL/O+rnj94JRGwt/CHbc9+6EA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
        lucide.createIcons();
    </script>
    <script>
        const sound = new Howl({
            src: ['http://localhost:9000/SoundHelix-Song-1.mp3'],
            loop: true,
        });

        // 全局只创建一次
        const audioCtx = Howler.ctx;
        const analyser = audioCtx.createAnalyser();
        analyser.fftSize = 2048; // 可调：512/1024/2048
        analyser.smoothingTimeConstant = 0.8; // 平滑一点，柱子更自然

        // 连接到 Howler 输出
        Howler.masterGain.connect(analyser);
        analyser.connect(audioCtx.destination);

        let wired = false;

        function ensureWired() {
            if (wired) return;
            // 如有需要，恢复 AudioContext
            if (audioCtx.state === 'suspended') {
                audioCtx.resume();
            }

            try {
                // 断开 masterGain 之前到 destination 的直连
                Howler.masterGain.disconnect();
            } catch (e) {
                // 有的环境断不开也没关系
            }

            // 串联：masterGain -> analyser -> destination
            Howler.masterGain.connect(analyser);
            analyser.connect(audioCtx.destination);

            wired = true;
        }

        sound.on('load', () => {
            const soundDuration = sound.duration();

            // convert into mm:ss   
            const minutes = Math.floor(soundDuration / 60);
            const seconds = Math.floor(soundDuration % 60);
            const soundDurationText = `${minutes}:${seconds.toString().padStart(2, '0')}`;

            document.getElementById('sound-duration').textContent = soundDurationText;
            document.getElementById('sound-progress').max = soundDuration;
        });


        var soundId = null;
        var lastSpectrumData = null; // 存储最后一次的频谱数据

        /**
         * 播放或暂停音频
         */
        function playOrPause() {
            var button = document.querySelector('#play-pause-button');

            // 获取当前按钮的图标状态
            var currentIcon = button.getAttribute('data-lucide');

            if (currentIcon === 'play') {
                ensureWired(); // ✅ 串联 analyser + resume
                if (soundId === null) {
                    soundId = sound.play();
                } else {
                    sound.play(soundId);
                }
                button.setAttribute('data-lucide', 'pause');
                startTimer();
            } else if (currentIcon === 'pause') {
                sound.pause(soundId);
                button.setAttribute('data-lucide', 'play');
                stopTimer();
                // 暂停时显示最后一帧的波形状态
                drawWave(lastSpectrumData);
            }

            // 重新初始化 Lucide 图标以显示新的图标
            lucide.createIcons();
        };

        function seek(pos) {
            sound.seek(pos);
        }

        function startTimer() {
            timer = setInterval(() => {
                const pos = sound.seek(soundId) || 0;

                const dataArray = getAudioSpectrumData(60);
                console.log('dataArray', dataArray); // ✅ 这里就有值了

                // 存储当前频谱数据
                lastSpectrumData = dataArray;

                // 使用真实的频谱数据更新波形图
                drawWave(dataArray);

                document.getElementById('sound-progress').value = pos;

            }, 50);
        }

        function stopTimer() {
            clearInterval(timer);
        }

        // 绘制音频波形柱子
        function drawWave(spectrumData = null) {
            const canvas = document.getElementById('waveCanvas');
            const ctx = canvas.getContext('2d');

            // 清除画布
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            // 设置柱子参数
            const barCount = 60;
            const gap = 2; // 柱子间距
            const availableWidth = canvas.width;
            const totalGapWidth = (barCount - 1) * gap;
            const barWidth = (availableWidth - totalGapWidth) / barCount;
            const startX = 0;

            // 设置绿色
            ctx.fillStyle = '#22c55e'; // Tailwind green-500

            // 绘制60个柱子
            for (let i = 0; i < barCount; i++) {
                const x = startX + i * (barWidth + gap);
                
                let height;
                if (spectrumData && spectrumData.length >= barCount) {
                    // 使用真实的音频频谱数据 (0-255) 转换为柱子高度，增大跳动幅度
                    const rawValue = spectrumData[i];
                    
                    if (rawValue < 10) {
                        // 非常低的值保持最小高度
                        height = 3;
                    } else {
                        // 应用指数变换增强高值的对比度，创造更强的节奏感
                        const normalizedValue = rawValue / 255;
                        const enhanced = Math.pow(normalizedValue, 0.6); // 指数变换增强中高频
                        height = enhanced * 55 + 3; // 3-58 像素范围，更大的动态范围
                    }
                } else {
                    // 初始状态显示最低高度
                    height = 3;
                }
                
                const y = (canvas.height - height) / 2;
                ctx.fillRect(x, y, barWidth, height);
            }
        }


        // 3) 频谱采样函数：把当前时间点的频谱压缩成 N 段
        function getAudioSpectrumData(bars = 60) {
            if (!Howler.usingWebAudio || !wired) return new Uint8Array(bars); // 保底：全0
            const n = analyser.frequencyBinCount; // fftSize / 2
            const raw = new Uint8Array(n);
            analyser.getByteFrequencyData(raw);

            const bucket = Math.max(1, Math.floor(n / bars));
            const out = new Uint8Array(bars);
            for (let i = 0; i < bars; i++) {
                let sum = 0,
                    start = i * bucket,
                    end = Math.min(n, start + bucket);
                for (let j = start; j < end; j++) sum += raw[j];
                const avg = sum / (end - start); // 0~255
                
                // 增强动态范围：应用对数变换增强节奏感
                const enhanced = Math.min(255, avg * 1.5);
                out[i] = enhanced;
            }
            return out;
        }


        // 页面加载完成后绘制波形
        document.addEventListener('DOMContentLoaded', function() {
            drawWave();



            // 可选：定期更新波形以模拟动态效果
            // setInterval(drawWave, 200);
        });
    </script>
</body>

</html>
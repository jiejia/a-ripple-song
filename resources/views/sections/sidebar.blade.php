<div>
    <label class="input">
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
        <input type="search" required placeholder="Search" />
    </label>
</div>
@php(dynamic_sidebar('sidebar-primary'))
<div class="card bg-base-100 text-secondary-content w-full mt-4">
    <div class="card-body p-4">
        <div class="grid grid-cols-[60px_1fr] gap-4 items-center bg-base-300/50 p-4 rounded-lg">
            <div>
                <img src="https://images.unsplash.com/photo-1478737270239-2f02b77fc618?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=100&q=80" alt="Podcast 1" class="w-15 h-15 rounded-md" />
            </div>
            <div>
                <h4 class="text-md font-bold">Podcast 1</h4>
                <p class="text-xs text-base-content/80">
                    <span>October 28, 2025</span>               
                </p>
                <p class="text-xs text-base-content/50">
                    <span>100k views</span>               
                </p>
            </div>
        </div>
        <div class="h-[129px]" id="wave">
            
        </div>
        <div class="mt-0 w-full">
            <div class="grid grid-cols-[1fr_1fr]">
                <span>0:00</span>
                <span class="justify-self-end" id="sound-duration">0:00</span>
            </div>
            <input type="range" min="0" max="100" value="0" id="sound-progress" class="range range-xs w-full" oninput="seek(this.value)" />
        </div>
        <div class="mt-4 grid grid-cols-[1fr_1fr_1fr] gap-4 items-center w-full">
            <div>
                <i data-lucide="list-music" class="cursor-pointer w-4 h-4"></i>
            </div>
            <div class="flex justify-center gap-4 items-center">
                <i data-lucide="skip-back" class="cursor-pointer w-4 h-4"></i>
                <i data-lucide="play" class="cursor-pointer w-4 h-4 bg-success-500 rounded-full" data-type="play" id="play-pause-button" onclick="playOrPause()"></i>
                <i data-lucide="skip-forward" class="cursor-pointer w-4 h-4"></i>
            </div>
            <div class="justify-self-end">
                <i data-lucide="volume" class="cursor-pointer w-4 h-4"></i>
            </div>
        </div>
    </div>
</div>
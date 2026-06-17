{{-- Image Lightbox Modal --}}
<dialog id="image-lightbox-modal" class="modal modal-bottom sm:modal-middle">
  <div class="modal-box max-w-7xl w-full bg-base-100 p-2">
    <form method="dialog">
      <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2 z-10">✕</button>
    </form>
    <div class="flex items-center justify-center">
      <img id="lightbox-image" src="" alt="" class="max-h-[85vh] w-auto rounded-lg">
    </div>
  </div>
  <form method="dialog" class="modal-backdrop">
    <button>close</button>
  </form>
</dialog>


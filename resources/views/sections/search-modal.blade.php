<input type="checkbox" id="search-modal" class="modal-toggle" />
<div class="modal" role="dialog">
  <div class="modal-box">
  @php(get_search_form())
  </div>
  <label class="modal-backdrop" for="search-modal">{!! __('Close', 'sage') !!}</label>
</div>
<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="modal fade" id="ifkPreviewModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><i class="fa fa-eye"></i> Xem trước Email</h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label>Subject (đã render token)</label>
          <input type="text" class="form-control" id="previewSubject" readonly>
        </div>
        <label>HTML (đã render token)</label>
        <div id="previewHtml" style="border:1px solid #e6e6e6;border-radius:6px;min-height:380px;overflow:auto;background:#fff;padding:12px;"></div>
        <hr />
        <p class="text-muted" style="margin:0;">Lưu ý: Preview chỉ dùng dữ liệu demo / dữ liệu sinh viên-đơn tuyển bạn chọn, để bạn xác định token đúng.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Đóng</button>
      </div>
    </div>
  </div>
</div>

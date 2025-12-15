{{-- Modal Xác nhận Xóa - Dùng chung cho toàn bộ ứng dụng --}}
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center pt-0">
                <div class="mb-4">
                    <i class="bi bi-exclamation-triangle text-danger" style="font-size: 4rem;"></i>
                </div>
                <h4 class="modal-title mb-2" id="deleteModalLabel">Xác nhận xóa</h4>
                <p class="text-muted mb-0" id="deleteModalMessage">Bạn có chắc chắn muốn xóa mục này?</p>
                <p class="text-danger small mt-2">
                    <i class="bi bi-info-circle me-1"></i>
                    Hành động này không thể hoàn tác!
                </p>
            </div>
            <div class="modal-footer border-0 justify-content-center pt-0">
                <button type="button" class="btn btn-secondary px-4" data-coreui-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i> Hủy
                </button>
                <form id="deleteForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger px-4">
                        <i class="bi bi-trash me-1"></i> Xóa
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Hàm gọi modal xóa - dùng chung
    function confirmDelete(url, message = null) {
        const modal = document.getElementById('deleteModal');
        const form = document.getElementById('deleteForm');
        const messageEl = document.getElementById('deleteModalMessage');
        
        // Set action URL
        form.action = url;
        
        // Set custom message nếu có
        if (message) {
            messageEl.textContent = message;
        } else {
            messageEl.textContent = 'Bạn có chắc chắn muốn xóa mục này?';
        }
        
        // Show modal
        const deleteModal = new coreui.Modal(modal);
        deleteModal.show();
    }
</script>


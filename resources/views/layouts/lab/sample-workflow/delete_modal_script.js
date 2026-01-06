
// JavaScript for Generic Delete Modal
document.addEventListener('DOMContentLoaded', function () {
    $(document).off('click', '.delete-attachment-btn').on('click', '.delete-attachment-btn', function () {
        var attachmentId = $(this).data('id');
        var attachmentTitle = $(this).data('title');

        // Update Modal Content
        $('#delete-attachment-id').val(attachmentId);
        $('#delete-attachment-title').text(attachmentTitle);

        // Open Modal
        $('#delete-attachment-modal').modal('show');
    });
});

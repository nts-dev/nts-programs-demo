statusContentCell.attachURL("views/frames/save_satus_content.php", false, {
    report_content: '',
    height: (statusContentCell.getHeight()) / 1.2
});

statusEditorLayout.attachEvent("onContentLoaded", function (id) {
    statusContentIframe = statusEditorLayout.cells(id).getFrame();
});
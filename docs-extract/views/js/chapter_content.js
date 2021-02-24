tocContentCell.attachURL("views/frames/toc_content.php", false, {
    report_content: '',
    height: (tocContentCell.getHeight()) / 1.2
});

tocEditorLayout.attachEvent("onContentLoaded", function (id) {
    tocContentIframe = tocEditorLayout.cells(id).getFrame();
});
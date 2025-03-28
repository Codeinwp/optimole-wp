
jQuery(document).ready(function($) {
  console.log(OMAttachmentEdit);
  const existingFileName = $("#optml_rename_file").attr("placeholder");
  const renameBtn = $("#optml-rename-file-btn");

  renameBtn.on("click", function(e) {
    e.preventDefault();
    $('#publish').click();
  });

  $("#optml_rename_file").on("input", function(e) {
    console.log("change");
    const newFileName = $(this).val();
    if (newFileName === existingFileName) {
      return;
    }

    if(newFileName.trim().length === 0 || newFileName.trim().length > 100) {
      renameBtn.prop("disabled", true);
      return; 
    }

    renameBtn.prop("disabled", false);
  });

  $("#optml-replace-file-field").on("change", function(e) {
    handleFileSelect(this.files[0]);
  });

  $("#optml-replace-file-btn").on("click", function(e) {
    e.preventDefault();
    uploadFile();
  });

  $("#optml-replace-clear-btn").on("click", function(e) {
    e.preventDefault();
    clearFile();
  });
  
  const dropArea = document.getElementById("optml-file-drop-area");
  
  ["dragenter", "dragover", "dragleave", "drop"].forEach(event => {
    dropArea.addEventListener(event, preventDefaults, false);
  });
  
  function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
  }
  
  ["dragenter", "dragover"].forEach(event => {
    dropArea.addEventListener(event, highlight, false);
  });
  
  ["dragleave", "drop"].forEach(event => {
    dropArea.addEventListener(event, unhighlight, false);
  });
  
  function highlight() {
    dropArea.classList.add("drag-active");
  }
  
  function unhighlight() {
    dropArea.classList.remove("drag-active");
  }
  
  dropArea.addEventListener("drop", handleDrop, false);
  
  function handleDrop(e) {
    const dt = e.dataTransfer;
    const file = dt.files[0];
    handleFileSelect(file);
  }
  
  function handleFileSelect(file) {
    $(".optml-replace-file-error").addClass("hidden");
    
    if(!file) return;
    
    // Check file size
    if(file.size > OMAttachmentEdit.maxFileSize) {
      $(".optml-replace-file-error").removeClass("hidden");
      $(".optml-replace-file-error").text(OMAttachmentEdit.i18n.maxFileSizeError);
      $("#optml-replace-file-btn").prop("disabled", true);
      $("#optml-replace-file-field").val("");
      return;
    }
    
    // Set the file in the input
    if (file instanceof File) {
      const dataTransfer = new DataTransfer();
      dataTransfer.items.add(file);
      document.getElementById("optml-replace-file-field").files = dataTransfer.files;
    }
    
    // Enable button and update UI
    $("#optml-replace-file-btn").prop("disabled", false);
    $("#optml-replace-clear-btn").prop("disabled", false);
    $(".label-text").hide();

    const type = file.type;
    let showPreview = type.startsWith("image/");
    let html = showPreview ? "<img src='" + URL.createObjectURL(file) + "' />" : "";
    html += "<span>" + file.name + "</span>";
    
    $(".optml-replace-file-preview").html(html);
  }
  
  function uploadFile() {
    var formData = new FormData();
    formData.append("action", "optml_replace_file");
    formData.append("attachment_id", OMAttachmentEdit.attachmentId);
    formData.append("file", $("#optml-replace-file-field")[0].files[0]);

    console.log({
      action: "optml_replace_file",
      attachment_id: OMAttachmentEdit.attachmentId,
      file: $("#optml-replace-file-field")[0].files[0]
    })

    console.log(formData);

    jQuery.ajax({
      url: OMAttachmentEdit.ajaxURL,
      type: "POST",
      data: formData,
      processData: false,
      contentType: false,
      success: function(response) {
        console.log(response);
        if(response.success) {
          // window.location.reload();
          console.log(response);
        } else {
          $(".optml-replace-file-error").removeClass("hidden");
          $(".optml-replace-file-error").text(response.message);
        }
      },
      error: function(response) {
        console.log(response);
        $(".optml-replace-file-error").removeClass("hidden");
        $(".optml-replace-file-error").text(OMAttachmentEdit.i18n.replaceFileError);
      }
    });
  }

  function clearFile() {
    $(".optml-replace-file-preview").html("");
    $(".label-text").show();
    $("#optml-replace-file-btn").prop("disabled", true);
    $("#optml-replace-clear-btn").prop("disabled", true);
    $("#optml-replace-file-field").val("");
  }
});
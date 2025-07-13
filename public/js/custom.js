$(document).ready(function () {
    "use strict";
    select2();
    datatable();
    ckediter();
    setInterval(() => {
        feather.replace();
    }, 1000);
});

$(document).on("click", ".customModal", function () {
    "use strict";
    var modalTitle = $(this).data("title");
    var modalUrl = $(this).data("url");
    var modalSize = $(this).data("size") == "" ? "md" : $(this).data("size");
    $("#customModal .modal-title").html(modalTitle);
    $("#customModal .modal-dialog").addClass("modal-" + modalSize);
    $.ajax({
        url: modalUrl,
        success: function (result) {
            if (result.status == "error") {
                notifier.show(
                    "Error!",
                    result.messages,
                    "error",
                    errorImg,
                    4000
                );
            } else {
                $("#customModal .modal-body").html(result);
                $("#customModal").modal("show");
                select2();
                ckediter();
            }
        },
        error: function (result) {
            notifier.show(
                "Error!",
                "Failed to load content",
                "error",
                errorImg,
                4000
            );
        },
    });
});

// basic message
$(document).on("click", ".confirm_dialog", function (e) {
    "use strict";
    e.preventDefault();
    var dialogForm = $(this).closest("form");
    Swal.fire({
        title: "Are you sure you want to delete this expense?",
        text: "This expense cannot be restored after deletion.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, delete it!"
    }).then((result) => {
        if (result.isConfirmed) {
            dialogForm.submit();
        }
    });
});

// common
$(document).on("click", ".common_confirm_dialog", function (e) {
    "use strict";
    var dialogForm = $(this).closest("form");
    var actions = $(this).data("actions");
    Swal.fire({
        title: "Are you sure you want to delete " + actions + " ?",
        text:
            "This " +
            actions +
            " can not be restore after delete. Do you want to confirm?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes",
    }).then((data) => {
        if (data.isConfirmed) {
            dialogForm.submit();
        }
    });
});

$(document).on("click", ".fc-day-grid-event", function (e) {
    "use strict";
    e.preventDefault();
    var event = $(this);
    var modalTitle = $(this).find(".fc-content .fc-title").html();
    var modalSize = "md";
    var modalUrl = $(this).attr("href");
    $("#customModal .modal-title").html(modalTitle);
    $("#customModal .modal-dialog").addClass("modal-" + modalSize);
    $.ajax({
        url: modalUrl,
        success: function (result) {
            $("#customModal .modal-body").html(result);
            $("#customModal").modal("show");
        },
        error: function (result) {},
    });
});

function toastrs(title, message, status) {
    "use strict";
    if (status == "success") {
        notifier.show("Success!", message, "success", successImg, 4000);
    } else {
        notifier.show("Success!", message, "success", errorImg, 4000);
    }
}

function convertArrayToJson(form) {
    "use strict";
    var data = $(form).serializeArray();
    var indexed_array = {};

    $.map(data, function (n, i) {
        indexed_array[n["name"]] = n["value"];
    });

    return indexed_array;
}

function select2() {
    "use strict";
    if ($(".basic-select").length > 0) {
        $(".basic-select").each(function () {
            var basic_select = new Choices(this, {
                searchEnabled: false,
                removeItemButton: false,
            });
        });
    }

    if ($(".hidesearch").length > 0) {
        $(".hidesearch").each(function () {
            var basic_select = new Choices(this, {
                searchEnabled: false,
                removeItemButton: true,
            });
        });
    }
}

function ckediter(editer_id = "") {
    if (editer_id == "") {
        editer_id = "#classic-editor";
    }
    if ($(editer_id).length > 0) {
        ClassicEditor.create(document.querySelector(editer_id), {
            // Add configuration options here
            // height: '300px', // Example height, adjust as needed
        })
            .then((editor) => {
                // Set the minimum height directly // editor.ui.view.editable.element.style.minHeight = '300px';
            })
            .catch((error) => {
                console.error(error);
            });
    }
}
function datatable() {
    "use strict";

    if ($(".basic-datatable").length > 0) {
        $(".basic-datatable").DataTable({
            scrollX: true,
            dom: "Bfrtip",
            buttons: ["copy", "csv", "excel", "print"],
        });
    }

    // if ($(".advance-datatable").length > 0) {
    //     $(".advance-datatable").DataTable({
    //         scrollX: true,
    //         stateSave: false,
    //         dom: "Bfrtip",
    //         buttons: [
    //             {
    //                 extend: "excelHtml5",
    //                 exportOptions: {
    //                     columns: ":visible",
    //                 },
    //             },
    //             {
    //                 extend: "pdfHtml5",
    //                 exportOptions: {
    //                     columns: ":visible",
    //                 },
    //             },
    //             {
    //                 extend: "copyHtml5",
    //                 exportOptions: {
    //                     columns: ":visible",
    //                 },
    //             },
    //             "colvis",
    //         ],
    //     });
    // }
}

$(document).on("submit", "form[action*='expense'][method='delete'], form[action*='expense'][method='DELETE']", function(e) {
    e.preventDefault();
    var form = this;
    Swal.fire({
        title: "Are you sure you want to delete this expense?",
        text: "This expense cannot be restored after deletion.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, delete it!"
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
});

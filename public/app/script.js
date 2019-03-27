/**
 * Get parameter by name
 * 
 * @param {string} name 
 */
function getParameterByName(name) {
	name = name.replace(/[\[]/, "\\\[").replace(/[\]]/, "\\\]");
	var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
	results = regex.exec(location.search);
	return results == null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
}

/**
 * jQuery: Show all tasks
 */
function initialize() {
    $.ajax(`http://localhost/tasks?token=843n6iNmfBnM423DTjM3H4a7wNt3QuGe`, {
        success: function (data) {
            for (let i = 0; i < data.length; i++) {
                let date = (new Date(data[i].task_at)).toLocaleString()
                $(`.table-body`).append(
                    `<tr class="row" data-id="${data[i].id}">
                        <td>${data[i].id}</td>
                        <td>${data[i].name}</td>
                        <td>${date}</td>
                        <td>${data[i].person}</td>
                        <td>
                            <button name="modify" class="modify" data-id="${data[i].id}" disabled readonly>
                                <i style="font-size: 32px;" class="fas fa-pencil-alt fa-"></i>
                            </button>
                            <button name="delete" class="delete" data-id="${data[i].id}" disabled readonly>
                                <i style="font-size: 32px;" class="fas fa-eraser"></i>
                            </button>
                        </td>
                    </tr>`)
            }
        }
    })
}


/**
 * jQuery: Call anonym function
 */
$(function () {

    /**
     * jQuery: Get tasks
     */
    $(document).ready(function () {
        initialize()
        $.ajax(`http://localhost/persons?token=843n6iNmfBnM423DTjM3H4a7wNt3QuGe`, {
            success: function (data) {
                let t = "";
                data.forEach(function (item) { t +=`<option value="`+ item.id +`">`+item.person+`</option>` })
                $(`#name-group`).after(
                `<div class="input-group" id="person-group">
                    <select name="person" id="person" class="input-control"><option>-- Choississez une personne --</option>${t}</select>
                </div>`
                )
            }
        })
    })

    $(document).on(`click`, `.row`, function (e) {
        e.preventDefault()
        let id = $(this).attr(`data-id`)
        $.ajax(`http://localhost/tasks/${id}?token=843n6iNmfBnM423DTjM3H4a7wNt3QuGe`, {
            type: `GET`,
            success: function (data) {
                for (let i = 0; i < data.length; i++) {
                    $(`.table-body`).prepend(
                    `<tr>
                        <form method="post" id="update-form">
                            <td>${data[i].id}</td>
                            <td><input type="text" id="u-name" name="name" value="${data[i].name}"></td>
                            <td><input type="datetime-local" id="u-task_at" name="task_at" value="${data[i].task_at}"></td>
                            <td>${data[i].person}</td>
                            <td>
                                <input type="hidden" id="id" name="id" value="${data[i].id}">
                                <input type="hidden" id="user_id" name="user_id" value="${data[i].user_id}">
                                <button type="submit" name="update" id="update">
                                    <i style="font-size: 32px;" class="fas fa-pencil-alt fa-"></i>
                                </button>
                                <button data-id="${data[i].id}" name="delete" id="delete">
                                    <i style="font-size: 32px;" class="fas fa-eraser"></i>
                                </button>
                            </td>
                        </form>
                    </tr>`)
                }
            }
        })
    })

    $(document).on(`click`, `#delete`, function (e) {
        e.preventDefault()
        let id = $(this).attr(`data-id`)
        console.log(id)
        $.ajax(`http://localhost/tasks/delete/${id}?token=843n6iNmfBnM423DTjM3H4a7wNt3QuGe`, {
            type: `DELETE`,
            success: function (data) {
                console.info(data)
                alert(data.success)
                window.location.reload()
            }
        })
    })

    $(document).on(`click`, `#update`, function (event) {
        $(`#update-form`).submit(function (e) {
            e.preventDefault()
        })
        let name = $(`#u-name`).val()
        let task_at = $(`#u-task_at`).val()
        let id = $(`#id`).val()
        let user_id = $(`#user_id`).val()
        console.log(name, task_at, id)
        $.ajax(`http://localhost/tasks/update/${id}?name=${name}&date=${task_at}&person=${user_id}?token=843n6iNmfBnM423DTjM3H4a7wNt3QuGe`, {
            type: `PUT`,
            success: function (data) {
                console.info(data)
                alert(data.success)
                window.location.reload()
            }
        })
    })

    $(`.form-add`).submit(function (e) {
        e.preventDefault()
        let name = $(`#name`).val()
        let task_at = $(`#task_at`).val()
        let person = $(`#person`).val()
        console.log(name, task_at)
        $.ajax(`http://localhost/tasks/new?name=${name}&date=${task_at}&person=${person}?token=843n6iNmfBnM423DTjM3H4a7wNt3QuGe`, {
            type: `POST`,
            success: function (data) {
                console.info(data)
                alert(data.success)
                window.location.reload()
            }
        })
    })

})

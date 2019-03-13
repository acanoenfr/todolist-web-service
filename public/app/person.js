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
 * jQuery: Show all persons
 */
function initialize() {
    $.ajax(`http://localhost/persons`, {
        success: function (data) {
            for (let i = 0; i < data.length; i++) {
                let date = (new Date(data[i].task_at)).toLocaleString()
                $(`.table-body`).append(
                    `<tr class="row" data-id="${data[i].id}">
                        <td>${data[i].id}</td>
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
    })

    $(document).on(`click`, `.row`, function (e) {
        e.preventDefault()
        let id = $(this).attr(`data-id`)
        $.ajax(`http://localhost/persons/${id}`, {
            type: `GET`,
            success: function (data) {
                for (let i = 0; i < data.length; i++) {
                    $(`.table-body`).prepend(
                        `<tr>
                        <form method="post" id="update-form">
                            <td>${data[i].id}</td>
                            <td><input type="text" id="u-person" name="person" value="${data[i].person}"></td>
                            <td>
                                <input type="hidden" id="id" name="id" value="${data[i].id}">
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
        $.ajax(`http://localhost/persons/delete/${id}`, {
            type: `DELETE`,
            success: function (data) {
                console.info(data)
                alert(data.success)
                initialize()
            }
        })
    })

    $(document).on(`click`, `#update`, function (event) {
        $(`#update-form`).submit(function (e) {
            e.preventDefault()
        })
        let id = $(`#id`).val()
        let person = $(`#u-person`).val()
        console.log(person, id)
        $.ajax(`http://localhost/persons/update/${id}?person=${person}`, {
            type: `PUT`,
            success: function (data) {
                console.info(data)
                alert(data.success)
                initialize()
            }
        })
    })

    $(`.form-add`).submit(function (e) {
        e.preventDefault()
        let person = $(`#person`).val()
        console.log(person)
        $.ajax(`http://localhost/persons/new?person=${person}`, {
            type: `POST`,
            success: function (data) {
                console.info(data)
                alert(data.success)
                initialize()
            }
        })
    })

})

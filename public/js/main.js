const users = document.getElementById("users");

if (users) {
    users.addEventListener('click', e => {
        if (e.target.className === 'btn btn-danger delete-user') {
            if (confirm('Ben je zeker?')) {
                const id = e.target.getAttribute('data-id');

                fetch(`/user/delete/${id}`, {
                    method: 'DELETE'
                }).then(res => window.location.reload());
            }
        }
    });
}

const messages = document.getElementById("messages");

if (messages) {
    messages.addEventListener('click', e => {
        if (e.target.className === 'btn btn-danger delete-message') {
            if (confirm('Ben je zeker?')) {
                const id = e.target.getAttribute('data-id');

                fetch(`/message/delete/${id}`, {
                    method: 'DELETE'
                }).then(res => window.location.reload());
            }
        }
    });
}

const homepageMessageTable = document.getElementById("homepageMessageTable");

if (homepageMessageTable) {
    homepageMessageTable.addEventListener('click', e => {
        if (e.target.className === 'homepage-messagetable') {
            const id = e.target.getAttribute('data-id');

            window.location.replace(`/message/${id}/comments`);
        }
    });
}

const addCommentButton = document.getElementById("addCommentButton");

if (addCommentButton) {
    addCommentButton.addEventListener('click', e => {
        const text = document.getElementById("commentInput").value;
        const id = e.target.getAttribute('data-id');

        var http = new XMLHttpRequest();
        var url = "/message/" + id + "/comment/new";
        var params = 'content=' + text;
        http.open('POST', url, true);

//Send the proper header information along with the request
        http.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

        http.onreadystatechange = function () {//Call a function when the state changes.
            if (http.readyState == 4 && http.status == 200) {
                alert("uw token is: " + http.responseText);
                window.location.replace(`/message/${id}/comments`);
            }
        };
        http.send(params);

    });
}


const comments = document.getElementById("comments");

if (comments) {
    comments.addEventListener('click', e => {
        if (e.target.className === 'btn btn-danger delete-comment') {

            var token = prompt("Please enter your token:", "Token...");
            const commentToken = e.target.getAttribute('data-id');

            if (token === commentToken) {
                fetch(`/comment/delete/${token}`, {
                    method: 'DELETE'
                }).then(res => window.location.reload());
            }
        }
        if (e.target.className === 'btn btn-primary edit-comment') {

            var token = prompt("Please enter your token:", "Token...");
            const commentToken = e.target.getAttribute('data-id');

            if (token === commentToken) {
                /*fetch(`/comment/edit/${token}`, {
                    method: 'GET'
                });
                alert(commentToken);*/
                window.location.replace(`/comment/edit/${token}`);
            } else {
                alert("wrong token");
            }
        }
    });
}

const searchButton = document.getElementById("searchButton");

if (searchButton) {
    searchButton.addEventListener('click', e => {

        var searchTerm = document.getElementById("searchInput").value;

        window.location.replace("/message/search/?searchTerm=" + searchTerm);

        //$.get("/message/search", {searchTerm: searchTerm});

    });
}





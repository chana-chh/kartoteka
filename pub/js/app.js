/*

*/
// Navbar dropdown
var dropdowns = document.querySelectorAll("nav ul li a:not(:only-child)");
dropdowns.forEach(function (elem) {
	elem.addEventListener("click", function (e) {
		var dropdownuls = document.querySelectorAll(".nav-dropdown");
		dropdownuls.forEach(function (el) {
			el.style.display = "none";
		});
		var ddul = this.nextElementSibling;
		if (ddul.style.display === "block") {
			ddul.style.display = "none"
		} else {
			ddul.style.display = "block"
		}
		e.stopPropagation();
	});
});
document.getElementsByTagName("html")[0].addEventListener("click", function () {
	var dropdownuls = document.querySelectorAll(".nav-dropdown");
	dropdownuls.forEach(function (el) {
		el.style.display = "none";
	});
});

// Pagination goto
var paginator_goto = document.querySelector("#pgn-goto");
if (paginator_goto !== null) {
	paginator_goto.addEventListener('change', function () {
		location = this.value;
	});
};

// Close button flash
var close = document.querySelectorAll(".flash .close");
close.forEach(function (el) {
	el.addEventListener("click", function () {
		this.parentElement.style.display = "none";
	});
});

// Close button modal
var close = document.querySelectorAll(".modal .close");
close.forEach(function (el) {
	el.addEventListener("click", function () {
		this.parentElement.parentElement.parentElement.parentElement.style.display = "none";
	});
});

// AJAX
function ajaxRequest(data) {
	var method = (typeof data.method !== 'undefined') ? data.method : "GET";
	var url = data.url;
	var async = (typeof data.async !== 'undefined') ? data.async : true;
	var element = data.element;
	var params = (method === "GET") ? "?" : "";
	if (typeof data.params !== 'undefined') {
		params = params + Object.keys(data.params).map(function (k) {
			return encodeURIComponent(k) + '=' + encodeURIComponent(data.params[k])
		}).join('&');
	}
	var xhr = new XMLHttpRequest();
	xhr.onreadystatechange = function () {
		if (xhr.readyState === 4 && xhr.status === 200) {
			result = JSON.parse(xhr.responseText);
			document.getElementById("csrf_name").value = result.csrf_name;
			document.getElementById("csrf_value").value = result.csrf_value;
			if (typeof element !== 'undefined') {
				document.getElementById(element).innerHTML = result.tekst;
			} else {
				// Izvrseno uspesno ali bez rezultata
				alert("AJAX IZVRSEN");
			}
		}
	};
	if (method === "POST") {
		xhr.open(method, url, async);
		xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
		xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		xhr.send(params);
	}
	if (method === "GET") {
		xhr.open(method, url + params, async);
		xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
		xhr.send();
	}
}

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
function ajaxFetch(url, options) {
	return fetch(url, options)
		.then(function (response) {
			if (!response.ok) {
				throw new Error('Fetch nije OK!');
			}
			return response.json();
		})
		.catch(function (err) {
			console.log('Fetch Error:', err)
		});
}

function formData(form) {
	let formData = new FormData(document.querySelector(form));
	formData.append("csrf_name", document.getElementById("csrf_name").value);
	formData.append("csrf_value", document.getElementById("csrf_value").value);
	return formData;
}

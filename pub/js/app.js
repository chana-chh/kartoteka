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

function ajaxFetch(url, options) {
	/*
		method:
			The request method, e.g., GET, POST. Note that the Origin header is not set on Fetch requests
			with a method of HEAD or GET (this behavior was corrected in Firefox 65 — seebug 1508661).
		headers:
			Any headers you want to add to your request, contained within a Headers object or an object
			literal with ByteString values. Note that some names are forbidden.
		body:
			Any body that you want to add to your request: this can be a Blob, BufferSource, FormData,
			URLSearchParams, or USVString object. Note that a request using the GET or HEAD method cannot have a body.
		mode:
			The mode you want to use for the request, e.g., cors, no-cors, or same-origin.
		credentials:
			The request credentials you want to use for the request: omit, same-origin, or include.
			To automatically send cookies for the current domain, this option must be provided. Starting with Chrome 50,
			this property also takes a FederatedCredential instance or a PasswordCredential instance.
		cache:
			The cache mode you want to use for the request.
		redirect:
			The redirect mode to use: follow (automatically follow redirects), error (abort with an error if a redirect occurs),
			or manual (handle redirects manually). In Chrome the default is follow (before Chrome 47 it defaulted to manual).
		referrer:
			A USVString specifying no-referrer, client, or a URL. The default is client.
		referrerPolicy:
			Specifies the value of the referer HTTP header. May be one of no-referrer, no-referrer-when-downgrade, origin,
			origin-when-cross-origin, unsafe-url.
		integrity:
			Contains the subresource integrity value of the request (e.g., sha256-BpfBw7ivV8q2jLiT13fxDYAe2tJllusRSZ273h2nFSE=).
		keepalive:
			The keepalive option can be used to allow the request to outlive the page. Fetch with the keepalive flag
			is a replacement for the Navigator.sendBeacon() API. 
		signal:
			An AbortSignal object instance; allows you to communicate with a fetch request and abort it if desired via an AbortController.
	*/

	return fetch("http://localhost/kartoteka/pub/ajax/post", options)
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
	return new FormData(document.querySelector(form));
}
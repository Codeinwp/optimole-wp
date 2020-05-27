(function(w, d) {
	w.addEventListener("load", async function () {
		if (typeof reportScript !== 'undefined') {
			let pageImages = document.getElementsByTagName('img');
			let imagesAdd = {};
			for (let i = 0; i < pageImages.length; i++) {
				if (pageImages[i].src.includes("0.gravatar.com")) {
					continue;
				}
				let words = pageImages[i].src.split('://');
				if (words.length <= 1) {
					continue;
				}
				let domain = words[words.length - 1].split('/')[0];
				if (!words[1].includes(reportScript.optmlCdn)) {
					if (imagesAdd.hasOwnProperty(domain)) {
						if (imagesAdd[domain].hasOwnProperty("ignoredUrls")) {
							imagesAdd[domain]["ignoredUrls"]++;
							continue;
						}
					}
					imagesAdd[domain] = Object.assign({ignoredUrls: 1}, imagesAdd[domain]);
					continue;
				}
				if (imagesAdd.hasOwnProperty(domain)) {
					if (imagesAdd[domain].hasOwnProperty("src")) {
						imagesAdd[domain]["src"].push(pageImages[i].src);
						continue;
					}
				}
				imagesAdd[domain] = Object.assign({src: Array(pageImages[i].src)}, imagesAdd[domain]);
			}
			fetch(reportScript.restUrl, {
				method: 'POST', // *GET, POST, PUT, DELETE, etc.
				mode: 'cors', // no-cors, *cors, same-origin
				cache: 'no-cache', // *default, no-cache, reload, force-cache, only-if-cached
				credentials: 'same-origin', // include, *same-origin, omit
				headers: {
					'X-WP-Nonce': reportScript.nonce,
					'Content-Type': 'application/json'
				},
				referrerPolicy: 'no-referrer', // no-referrer, *no-referrer-when-downgrade, origin, origin-when-cross-origin, same-origin, strict-origin, strict-origin-when-cross-origin, unsafe-url
				body: JSON.stringify({images: imagesAdd})
				// body data type must match "Content-Type" header
			}).then(response => {
				response.json().then(function (data) {
					let optmlAdmin = document.querySelector("li#wp-admin-bar-optml_report_script ul#wp-admin-bar-optml_report_script-default");
					switch (data.code) {
						case  "deactivated" : {
							optmlAdmin.innerHTML = "<li><div class='ab-item ab-empty-item'>Your account is permanently disabled<div><li>";
							break;
						}
						case  "log" : {
							let notice = "";
							for (let domain of Object.keys(data.data)) {
								if (data.data[domain] === "whitelist") {
									notice += "<li><div class='ab-item ab-empty-item'> The domain: " + domain + " is not added to the whitelist <div><li>";
								}
								if (data.data[domain] === "later") {
									notice += "<li><div class='ab-item ab-empty-item'> The images from: " + domain + " are scheduled to be processed soon<div><li>";
								}
							}
							optmlAdmin.innerHTML = notice;
							break;
						}
						default : {
							optmlAdmin.innerHTML = "<li><div class='ab-item ab-empty-item'>Everything is ok<div><li>";
						}
					}
				});
			});
		}
	});
}(window, document));
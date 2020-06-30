(function(w, d) {
	w.addEventListener("load", async function () {
		let optmlAdmin = document.querySelector("li#wp-admin-bar-optml_report_script ul#wp-admin-bar-optml_report_script-default");
		optmlAdmin.addEventListener("click", function () {
			if (typeof reportScript !== 'undefined') {
				let pageImages = document.getElementsByTagName('img');
				let imagesAdd = {};
				for (let i = 0; i < pageImages.length; i++) {
					let words = pageImages[i].src.split('://');
					if (words.length <= 1) {
						continue;
					}
					let domain = words[words.length - 1].split('/')[0];
					if (reportScript.ignoredDomains.includes(domain)) {
						continue;
					}
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
						let body = document.getElementsByTagName('body')[0];

						let modal = document.createElement('div');
						modal.setAttribute('class', 'optml-modal');
						let modalContent = document.createElement('div');
						modalContent.setAttribute('class', 'optml-modal-content');
						let modalClose = document.createElement('span');
						modalClose.setAttribute('class', 'optml-close');
						modalClose.innerHTML = "&times";
						modalClose.addEventListener("click" , function() {
							modal.style.display = "none";
						});
						let modalText = document.createElement('p');
						modalText.innerHTML =`${data.data}`;
						modalContent.appendChild(modalClose);
						modalContent.appendChild(modalText);
						modal.appendChild(modalContent);
						let report = '';
						let optmlAdmin = document.querySelector("li#wp-admin-bar-optml_report_script ul#wp-admin-bar-optml_report_script-default");
						modal.style.display = "block";
						w.addEventListener( "click", function(event) {
							if (event.target == modal) {
								modal.style.display = "none";
							}
						});
						body.appendChild(modal);
					});
				});
			}
		});
	});
}(window, document));
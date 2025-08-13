(function(){
	function qs(s,ctx){return (ctx||document).querySelector(s);} 
	function qsa(s,ctx){return (ctx||document).querySelectorAll(s);} 
	function normalizePhone(v){return (v||'').replace(/[^0-9+\-\s]/g,'');}
	function digits(v){return (v||'').replace(/\D+/g,'');}
	function scrollToEl(el){try{el.scrollIntoView({behavior:'smooth', block:'center'});}catch(e){}}
	function setMsg(el, text, isError){el.textContent=text||''; el.classList.toggle('error', !!isError); el.classList.toggle('success', !isError);}

	function handleForm(formId, submitAction){
		var form = qs('#' + formId);
		if(!form) return;
		var btn = qs('button[type="submit"]', form);
		var msg = qs('.purze-message', form);
		form.addEventListener('submit', function(e){
			e.preventDefault();
			setMsg(msg, '', false);
			if(btn){ btn.disabled = true; var old = btn.textContent; btn.dataset.old=old; btn.textContent='Sending…'; }

			var data = new FormData(form);
			var ph = normalizePhone(String(data.get('phone')||''));
			if(data.has('phone')){ data.set('phone', ph); }
			if(data.has('phone') && digits(ph).length < 7){
				setMsg(msg, 'Please enter a valid phone number (7+ digits).', true);
				if(btn){ btn.disabled=false; btn.textContent=btn.dataset.old; }
				scrollToEl(msg); return;
			}
			fetch(purzeAjax.url, { method: 'POST', credentials: 'same-origin', headers:{'X-WP-Nonce': purzeAjax.nonce}, body: data })
			.then(function(r){ return r.json(); })
			.then(function(res){
				if(res && res.success){
					setMsg(msg, res.data && res.data.message ? res.data.message : 'Thank you!');
					form.reset();
				} else {
					var err = (res && res.data && res.data.message) ? res.data.message : 'Submission failed. Please try again.';
					setMsg(msg, err, true);
					if(res && res.data && res.data.errors){
						var firstKey = Object.keys(res.data.errors)[0];
						var firstInput = qs('[name="'+firstKey+'"]', form);
						if(firstInput){ firstInput.focus(); }
					}
				}
				scrollToEl(msg);
			})
			.catch(function(){ setMsg(msg, 'Network error. Please try again.', true); })
			.finally(function(){ if(btn){ btn.disabled=false; btn.textContent=btn.dataset.old; } });
		});
	}
		document.addEventListener('DOMContentLoaded', function(){
		handleForm('purze-hero-form', 'purze_submit_lead');
		handleForm('purze-contact-form', 'purze_submit_contact');
	});
})();
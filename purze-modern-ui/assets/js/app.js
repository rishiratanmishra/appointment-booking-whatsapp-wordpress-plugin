(function(){
	function qs(s,ctx){return (ctx||document).querySelector(s);} 
	function qsa(s,ctx){return (ctx||document).querySelectorAll(s);} 
	function normalizePhone(v){return (v||'').replace(/[^0-9+\-\s]/g,'');}
	function digits(v){return (v||'').replace(/\D+/g,'');}
	function scrollToEl(el){try{el.scrollIntoView({behavior:'smooth', block:'center'});}catch(e){}}
	function setMsg(el, text, isError){el.textContent=text||''; el.classList.toggle('error', !!isError); el.classList.toggle('success', !isError);} 

	var SERVICES = [
		{ label: 'Web Design', value: 'web_design' },
		{ label: 'Graphic Design', value: 'graphic_design' },
		{ label: 'SEO', value: 'seo' },
		{ label: 'Digital Marketing', value: 'digital_marketing' },
		{ label: 'Other', value: 'other' }
	];

	document.addEventListener('DOMContentLoaded', function(){
		var y = qs('#year'); if (y) { y.textContent = new Date().getFullYear(); }
		var toggle = qs('#darkToggle');
		if (toggle) {
			var key = 'purze-ui-mode';
			var mode = localStorage.getItem(key);
			if (mode === 'dark') document.documentElement.setAttribute('data-theme','dark');
			toggle.addEventListener('click', function(){
				var isDark = document.documentElement.getAttribute('data-theme') === 'dark';
				if (isDark) { document.documentElement.removeAttribute('data-theme'); localStorage.setItem(key,'light'); toggle.setAttribute('aria-pressed','false'); }
				else { document.documentElement.setAttribute('data-theme','dark'); localStorage.setItem(key,'dark'); toggle.setAttribute('aria-pressed','true'); }
			});
		}

		// Populate services
		var svc = qs('#service');
		if (svc) {
			SERVICES.forEach(function(s){
				var opt = document.createElement('option');
				opt.value = s.value; opt.textContent = s.label; svc.appendChild(opt);
			});
		}

		// Form handling
		var form = qs('#lead-form');
		if (!form) return;
		var btn = qs('#submitBtn');
		var msg = qs('#formMsg');
		form.addEventListener('submit', function(e){
			e.preventDefault();
			setMsg(msg, '', false);
			if (btn){ btn.disabled = true; btn.dataset.old = btn.textContent; btn.textContent = 'Sending…'; }
			var data = new FormData(form);
			var ph = normalizePhone(String(data.get('phone')||''));
			if (data.has('phone')) data.set('phone', ph);
			if (digits(ph).length < 7) {
				setMsg(msg, 'Please enter a valid phone number (7+ digits).', true);
				if (btn){ btn.disabled = false; btn.textContent = btn.dataset.old; }
				scrollToEl(msg); return;
			}
			fetch('submit.php', {
				method: 'POST',
				credentials: 'same-origin',
				headers: { 'Accept': 'application/json' },
				body: data
			})
			.then(function(r){ return r.json(); })
			.then(function(res){
				if (res && res.success) {
					setMsg(msg, res.message || 'Thank you! We will be in touch shortly.');
					form.reset();
				} else {
					var err = res && res.message ? res.message : 'Submission failed. Please try again.';
					setMsg(msg, err, true);
					if (res && res.errors) {
						var key = Object.keys(res.errors)[0];
						var input = qs('[name="'+key+'"]', form); if (input) input.focus();
					}
				}
				scrollToEl(msg);
			})
			.catch(function(){ setMsg(msg, 'Network error. Please try again.', true); })
			.finally(function(){ if (btn){ btn.disabled = false; btn.textContent = btn.dataset.old; } });
		});
	});
})();
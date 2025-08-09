(function(){
  function qs(sel, ctx){ return (ctx||document).querySelector(sel); }
  function qsa(sel, ctx){ return Array.from((ctx||document).querySelectorAll(sel)); }

  function openModal(){
    const modal = qs('#baw-modal');
    if (!modal) return;
    modal.setAttribute('aria-hidden', 'false');
    qs('#baw-floating-button').setAttribute('aria-expanded','true');
  }
  function closeModal(){
    const modal = qs('#baw-modal');
    if (!modal) return;
    modal.setAttribute('aria-hidden', 'true');
    qs('#baw-floating-button').setAttribute('aria-expanded','false');
  }

  function cycleMessages(){
    const el = qs('.baw-animated');
    if (!el) return;
    try{
      const messages = JSON.parse(el.getAttribute('data-messages')||'[]');
      const speed = parseInt(el.getAttribute('data-speed')||'2500',10);
      if (!messages.length) return;
      let idx = 0;
      el.textContent = messages[idx];
      setInterval(()=>{
        idx = (idx+1) % messages.length;
        el.textContent = messages[idx];
      }, speed);
    }catch(e){}
  }

  async function submitForm(e){
    e.preventDefault();
    const form = e.target;
    const messageBox = qs('.baw-message', form);
    messageBox.textContent='';
    const fd = new FormData(form);
    fd.append('action', 'baw_submit_appointment');
    try {
      const res = await fetch(BAW_OPTIONS.ajax_url, { method: 'POST', body: fd });
      const json = await res.json();
      if (json.success) {
        messageBox.textContent = json.data.message || 'Submitted';
        if (json.data.wa_url) {
          window.open(json.data.wa_url, '_blank');
        }
        setTimeout(closeModal, 600);
        form.reset();
      } else {
        if (json.data && json.data.errors) {
          messageBox.textContent = json.data.errors.join('\n');
        } else {
          messageBox.textContent = 'Submission failed';
        }
      }
    } catch(err) {
      messageBox.textContent = 'Submission error';
    }
  }

  document.addEventListener('DOMContentLoaded', function(){
    const btn = qs('#baw-floating-button');
    if (btn) btn.addEventListener('click', openModal);
    qsa('[data-close="baw-modal"]').forEach(el=>el.addEventListener('click', closeModal));
    const form = qs('#baw-appointment-form');
    if (form) form.addEventListener('submit', submitForm);
    cycleMessages();
  });
})();



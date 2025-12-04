(function () {
			const form = document.getElementById('regForm');
			// If there's no registration form on this page, skip registration handlers
			if (!form) return;
			const fields = {
				fullName: {el: document.getElementById('fullName'), err: document.getElementById('err-fullName')},
				phone: {el: document.getElementById('phone'), err: document.getElementById('err-phone')},
				pharmacyName: {el: document.getElementById('pharmacyName'), err: document.getElementById('err-pharmacyName')},
				pharmacyAddress: {el: document.getElementById('pharmacyAddress'), err: document.getElementById('err-pharmacyAddress')}
			};

			function resetErrors() {
				Object.values(fields).forEach(f => f.err.textContent = '');
			}

			function validate() {
				let valid = true;
				resetErrors();

				if (!fields.fullName.el.value.trim()) {
					fields.fullName.err.textContent = 'Full name is required.';
					valid = false;
				}

				const phoneVal = fields.phone.el.value.trim();
				if (!phoneVal) {
					fields.phone.err.textContent = 'Phone number is required.';
					valid = false;
				} else {
					// basic phone validation: digits, spaces, +, -, parentheses
					const phonePattern = /^[0-9+()\-\s]{6,20}$/;
					if (!phonePattern.test(phoneVal)) {
						fields.phone.err.textContent = 'Enter a valid phone number (digits and + - ()).';
						valid = false;
					}
				}

				if (!fields.pharmacyName.el.value.trim()) {
					fields.pharmacyName.err.textContent = 'Pharmacy name is required.';
					valid = false;
				}

				if (!fields.pharmacyAddress.el.value.trim()) {
					fields.pharmacyAddress.err.textContent = 'Pharmacy address is required.';
					valid = false;
				}

				return valid;
			}

			form.addEventListener('submit', function (e) {
				e.preventDefault();
				if (validate()) {
					
					alert('Registration successful — thank you!');
					form.reset();
				} else {
					
					const firstErr = document.querySelector('.error:not(:empty)');
					if (firstErr) {
						const input = firstErr.previousElementSibling;
						if (input && typeof input.focus === 'function') input.focus();
					}
				}
			});

			const cancel = document.getElementById('cancelBtn');
			if (cancel) {
				cancel.addEventListener('click', function () {
					window.location.href = 'index.html';
				});
			}
		})();

		// Mobile nav toggle
		(function () {
			const navToggle = document.getElementById('navToggle');
			if (!navToggle) return;

			function openNav() {
				document.body.classList.add('nav-open');
				navToggle.setAttribute('aria-expanded', 'true');
			}

			function closeNav() {
				document.body.classList.remove('nav-open');
				navToggle.setAttribute('aria-expanded', 'false');
			}

			navToggle.addEventListener('click', function (e) {
				e.stopPropagation();
				if (document.body.classList.contains('nav-open')) closeNav(); else openNav();
				// Also collapse sidebar on small screens
				const layout = document.querySelector('.layout');
				if (layout && window.matchMedia('(max-width: 980px)').matches) {
					layout.classList.toggle('side-collapsed');
				}
			});

			// Close when clicking outside the nav panel
			document.addEventListener('click', function (e) {
				const links = document.querySelector('.links');
				if (!links) return;
				if (!links.contains(e.target) && !navToggle.contains(e.target)) closeNav();
			});

			document.addEventListener('keydown', function (e) {
				if (e.key === 'Escape') closeNav();
			});
		})();
	// Sidebar collapse toggle on dashboard
(function(){
    const btn = document.getElementById('sidebarToggle');
    const layout = document.querySelector('.layout');
    if (!btn || !layout) return;
    function collapse(){
        layout.classList.add('side-collapsed');
        btn.setAttribute('aria-expanded','false');
        const subs = document.querySelectorAll('.submenu');
        subs.forEach(s => s.classList.remove('show'));
        const toggles = document.querySelectorAll('.menu-item.has-sub');
        toggles.forEach(b => b.setAttribute('aria-expanded','false'));
    }
    function expand(){
        layout.classList.remove('side-collapsed');
        btn.setAttribute('aria-expanded','true');
    }
    btn.addEventListener('click', function(){
        if (layout.classList.contains('side-collapsed')) expand(); else collapse();
    });
})();



(function () {
    const btn = document.getElementById('profileBtn');
    const menu = document.getElementById('profileMenu');

		if (!btn || !menu) return;

		function openMenu() {
			menu.classList.add('show');
			btn.setAttribute('aria-expanded', 'true');
			menu.setAttribute('aria-hidden', 'false');
			document.body.classList.add('menu-open');
		}

		function closeMenu() {
			menu.classList.remove('show');
			btn.setAttribute('aria-expanded', 'false');
			menu.setAttribute('aria-hidden', 'true');
			document.body.classList.remove('menu-open');
		}

		btn.addEventListener('click', function (e) {
			e.stopPropagation();
			if (menu.classList.contains('show')) closeMenu(); else openMenu();
		});

	
		document.addEventListener('click', function (e) {
			if (!menu.contains(e.target) && !btn.contains(e.target)) closeMenu();
		});

	
		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape') closeMenu();
		});
})();


	(function () {
		const expandBtn = document.getElementById('expand-button');
		const content = document.querySelector('.features-content');
		if (!expandBtn || !content) return;

		function open() {
			expandBtn.setAttribute('aria-expanded', 'true');
			content.classList.add('open');
			
			content.style.maxHeight = content.scrollHeight + 'px';
		}

		function close() {
			expandBtn.setAttribute('aria-expanded', 'false');
	
			content.style.maxHeight = content.scrollHeight + 'px';
			requestAnimationFrame(() => {
				content.style.maxHeight = '0px';
			});
			content.classList.remove('open');
		}

		expandBtn.addEventListener('click', function () {
			const expanded = expandBtn.getAttribute('aria-expanded') === 'true';
			if (expanded) close(); else open();
		});
	})();

// Dashboard-specific: submenu toggles, theme toggle, and sidebar collapse
document.addEventListener('DOMContentLoaded', function(){
	// Submenu toggles
	const toggles = document.querySelectorAll('.menu-item.has-sub');
	toggles.forEach(function(btn){
		btn.addEventListener('click', function(){
			const id = btn.getAttribute('data-target');
			const sub = document.getElementById(id);
			if (!sub) return;
			const open = sub.classList.contains('show');
			if (open){ sub.classList.remove('show'); btn.setAttribute('aria-expanded','false'); }
			else { sub.classList.add('show'); btn.setAttribute('aria-expanded','true'); }
		});
	});

    // Theme toggle: default light, switch to dark on user click
    const themeBtn = document.getElementById('themeToggle');
    document.body.classList.remove('dark');
    if (themeBtn){
        themeBtn.innerHTML = '<i class="fas fa-moon"></i>';
        themeBtn.addEventListener('click', function(){
            const darkOn = document.body.classList.toggle('dark');
            try { localStorage.setItem('ps_theme', darkOn ? 'dark' : 'light'); } catch(e){}
            themeBtn.innerHTML = darkOn ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>';
        });
    }

	// Sidebar collapse (button exists on dashboard)
	const sideBtn = document.getElementById('sidebarToggle');
	const layout = document.querySelector('.layout');
	if (sideBtn && layout){
		sideBtn.addEventListener('click', function(){
			const collapsed = layout.classList.toggle('side-collapsed');
			sideBtn.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
			// rotate chevron
			try { sideBtn.innerHTML = collapsed ? '<i class="fas fa-angle-right"></i>' : '<i class="fas fa-angle-left"></i>'; } catch(e){}
		});
	}
});

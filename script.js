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
	const storageKey = 'ps_open_menu';
	function openMenuByKey(menuKey){
		if (!menuKey) return;
		const btn = document.querySelector('.menu-item.has-sub[data-menu="' + menuKey + '"]');
		if (!btn) return;
		const id = btn.getAttribute('data-target');
		const sub = document.getElementById(id);
		if (!sub) return;
		sub.classList.add('show');
		btn.classList.add('active');
		btn.setAttribute('aria-expanded','true');
	}

	// Submenu toggles
	const toggles = document.querySelectorAll('.menu-item.has-sub');
	toggles.forEach(function(btn){
		btn.addEventListener('click', function(){
			const id = btn.getAttribute('data-target');
			const sub = document.getElementById(id);
			if (!sub) return;
			const open = sub.classList.contains('show');
			if (open){
				sub.classList.remove('show');
				btn.setAttribute('aria-expanded','false');
				btn.classList.remove('active');
				const menuKey = btn.getAttribute('data-menu');
				const saved = localStorage.getItem(storageKey);
				if (saved && menuKey === saved) localStorage.removeItem(storageKey);
			} else {
				sub.classList.add('show');
				btn.setAttribute('aria-expanded','true');
				btn.classList.add('active');
				const menuKey = btn.getAttribute('data-menu');
				if (menuKey) localStorage.setItem(storageKey, menuKey);
			}
		});
	});

	const serverOpened = document.querySelector('.submenu.show');
	if (!serverOpened){
		openMenuByKey(localStorage.getItem(storageKey));
	}

	const submenuLinks = document.querySelectorAll('.submenu-item');
	submenuLinks.forEach(function(link){
		link.addEventListener('click', function(){
			const parentSub = link.closest('.submenu');
			if (!parentSub) return;
			const btn = document.querySelector('.menu-item.has-sub[data-target="' + parentSub.id + '"]');
			if (!btn) return;
			const menuKey = btn.getAttribute('data-menu');
			if (menuKey) localStorage.setItem(storageKey, menuKey);
		});
	});

	// Theme toggle with persistence
	const themeBtn = document.getElementById('themeToggle');
	const preferredDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
	let savedTheme = 'light';
	try {
		savedTheme = localStorage.getItem('ps_theme') || (preferredDark ? 'dark' : 'light');
	} catch(e) {}
	function applyTheme(mode){
		const darkOn = mode === 'dark';
		document.body.classList.toggle('dark', darkOn);
		if (themeBtn) themeBtn.innerHTML = darkOn ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>';
	}
	applyTheme(savedTheme);
	if (themeBtn){
		themeBtn.addEventListener('click', function(){
			savedTheme = savedTheme === 'dark' ? 'light' : 'dark';
			applyTheme(savedTheme);
			try { localStorage.setItem('ps_theme', savedTheme); } catch(e){}
		});
	}

	// Sidebar collapse (button exists on dashboard)
	const sideBtn = document.getElementById('sidebarToggle');
	const layout = document.querySelector('.layout');
	if (sideBtn && layout){
		const collapseSidebar = () => {
			layout.classList.add('side-collapsed');
			sideBtn.setAttribute('aria-expanded','false');
			try { sideBtn.innerHTML = '<i class="fas fa-angle-right"></i>'; } catch(e){}
			document.querySelectorAll('.submenu').forEach(s => s.classList.remove('show'));
			document.querySelectorAll('.menu-item.has-sub').forEach(btn => btn.setAttribute('aria-expanded','false'));
		};
		const expandSidebar = () => {
			layout.classList.remove('side-collapsed');
			sideBtn.setAttribute('aria-expanded','true');
			try { sideBtn.innerHTML = '<i class="fas fa-angle-left"></i>'; } catch(e){}
		};
		sideBtn.addEventListener('click', function(){
			if (layout.classList.contains('side-collapsed')) expandSidebar(); else collapseSidebar();
		});
	}
});

function initStaffModules(root){
	if (!root) return;
	const tabs = root.querySelectorAll('.staff-tabs .tab');
	const panels = root.querySelectorAll('.tab-panel');
	if (tabs.length) {
		tabs.forEach(tab => {
			tab.addEventListener('click', function(){
				const target = tab.getAttribute('data-tab');
				tabs.forEach(t=>t.classList.remove('active'));
				panels.forEach(p=>p.classList.remove('active'));
				tab.classList.add('active');
				const panel = root.querySelector('#' + target);
				if (panel) panel.classList.add('active');
			});
		});
	}

	const addBtn = root.querySelector('#addStaffBtn');
	const modal = root.querySelector('#addStaffModal');
	const modalCloses = modal ? modal.querySelectorAll('.modal-close') : [];
	const addForm = root.querySelector('#addStaffForm');
	const manageModal = root.querySelector('#manageStaffModal');
	const manageForm = root.querySelector('#manageStaffForm');
	const manageCloses = manageModal ? manageModal.querySelectorAll('.modal-close') : [];

	if (addBtn && modal) {
		addBtn.addEventListener('click', function(){
			modal.setAttribute('aria-hidden','false');
		});
		modalCloses.forEach(btn => btn.addEventListener('click', function(){ modal.setAttribute('aria-hidden','true'); }));
		modal.addEventListener('click', function(e){ if (e.target === modal) modal.setAttribute('aria-hidden','true'); });
	}

	if (addForm && modal) {
		addForm.addEventListener('submit', function(e){
			e.preventDefault();
			const formData = new FormData(addForm);
			fetch('handlers/add_staff.php', {
				method: 'POST',
				body: formData,
				credentials: 'same-origin'
			}).then(r => r.json()).then(data => {
				if (data && data.success) {
					modal.setAttribute('aria-hidden','true');
					alert('Staff account created. Temporary password: ' + (data.password || '(hidden)'));
					addForm.reset();
					window.location.reload();
				} else {
					alert('Error: ' + (data.error || 'Unknown error'));
				}
			}).catch(err => {
				console.error(err);
				alert('Request failed. See console for details.');
			});
		});
	}

	if (manageModal) {
		manageCloses.forEach(btn => btn.addEventListener('click', function(){ manageModal.setAttribute('aria-hidden','true'); }));
		manageModal.addEventListener('click', function(e){ if (e.target === manageModal) manageModal.setAttribute('aria-hidden','true'); });
	}

	if (manageForm && manageModal) {
		manageForm.addEventListener('submit', function(e){
			e.preventDefault();
			const formData = new FormData(manageForm);
			fetch('handlers/update_staff.php', {
				method: 'POST',
				body: formData,
				credentials: 'same-origin'
			}).then(r => r.json()).then(data => {
				if (data && data.success) {
					manageModal.setAttribute('aria-hidden','true');
					alert('Staff member updated successfully.');
					window.location.reload();
				} else {
					alert('Error: ' + (data.error || 'Unable to update staff member.'));
				}
			}).catch(err => {
				console.error(err);
				alert('Request failed. Please try again.');
			});
		});
	}

	bindStaffActionMenus();
}

function attachPartialNav(){
	const links = document.querySelectorAll('.submenu-item[data-partial="true"]');
	links.forEach(link => {
		if (link.dataset.partialBound === '1') return;
		link.dataset.partialBound = '1';
		link.addEventListener('click', function(e){
			if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
			e.preventDefault();
			const href = link.getAttribute('href');
			loadPartialPage(href, true);
		});
	});
}

function loadPartialPage(url, pushStateNeeded){
	const main = document.querySelector('.dash-wrap');
	if (!main) {
		window.location.href = url;
		return;
	}
	document.body.classList.add('content-loading');
	const partialUrl = url + (url.includes('?') ? '&' : '?') + 'partial=1';
	fetch(partialUrl, { headers: { 'X-Requested-With': 'fetch' } })
		.then(resp => {
			if (!resp.ok) throw new Error('Failed to load partial');
			return resp.text();
		})
		.then(html => {
			const temp = document.createElement('div');
			temp.innerHTML = html.trim();
			const newMain = temp.querySelector('.dash-wrap');
			if (!newMain) throw new Error('Invalid partial response');
			main.replaceWith(newMain);
			document.title = newMain.dataset.pageTitle || document.title;
			initStaffModules(newMain);
			document.body.classList.remove('content-loading');
			if (pushStateNeeded) {
				history.pushState({ partial: true, url: url }, '', url);
			}
		})
		.catch(err => {
			console.error(err);
			document.body.classList.remove('content-loading');
			window.location.href = url;
		});
}

document.addEventListener('DOMContentLoaded', function(){
	initStaffModules(document.querySelector('.dash-wrap'));
	attachPartialNav();
	if (!history.state) {
		history.replaceState({ partial: false, url: window.location.href }, '', window.location.href);
	}
});

window.addEventListener('popstate', function(event){
	if (event.state && event.state.partial) {
		loadPartialPage(event.state.url, false);
	}
});

let staffActionMenusBound = false;

function closeStaffActionMenus(){
	document.querySelectorAll('.staff-action-menu.show').forEach(menu => {
		menu.classList.remove('show');
		const trigger = menu.previousElementSibling;
		if (trigger && trigger.classList.contains('staff-action-trigger')) {
			trigger.setAttribute('aria-expanded', 'false');
		}
	});
}

function openManageStaffModal(payload, action){
	const modal = document.getElementById('manageStaffModal');
	const form = document.getElementById('manageStaffForm');
	const title = document.getElementById('manageStaffTitle');
	if (!modal || !form) return;
	form.reset();
	const idField = form.querySelector('input[name="staff_id"]');
	const primaryField = form.querySelector('input[name="is_primary_admin"]');
	const nameField = form.querySelector('input[name="fullname"]');
	const emailField = form.querySelector('input[name="email"]');
	const roleField = form.querySelector('select[name="role"]');
	const statusField = form.querySelector('select[name="status"]');
	if (idField) idField.value = payload.id || '';
	if (primaryField) primaryField.value = payload.isPrimary === '1' ? '1' : '0';
	if (nameField) nameField.value = payload.name || '';
	if (emailField) emailField.value = payload.email || '';
	if (roleField) {
		roleField.value = (payload.role || 'STAFF').toUpperCase();
	}
	if (statusField) {
		statusField.value = (payload.status || 'ACTIVE').toUpperCase();
	}
	if (payload.isPrimary === '1') {
		if (roleField) {
			roleField.value = 'ADMIN';
			roleField.setAttribute('disabled', 'disabled');
		}
		if (statusField) {
			statusField.value = 'ACTIVE';
			statusField.setAttribute('disabled', 'disabled');
		}
	} else {
		if (roleField) roleField.removeAttribute('disabled');
		if (statusField) statusField.removeAttribute('disabled');
	}
	if (title) {
		const labels = { role: 'Change Role', status: 'Update Status', edit: 'Edit Staff Info' };
		title.textContent = labels[action] || 'Manage Staff Member';
	}
	modal.setAttribute('aria-hidden', 'false');
	const focusTarget = action === 'role' && roleField
		? roleField
		: action === 'status' && statusField
		? statusField
		: nameField;
	if (focusTarget && typeof focusTarget.focus === 'function') {
		setTimeout(() => focusTarget.focus(), 80);
	}
}

function handleStaffAction(btn){
	const payload = {
		id: btn.dataset.staffId || '',
		name: btn.dataset.staffName || '',
		email: btn.dataset.staffEmail || '',
		role: (btn.dataset.staffRole || 'STAFF').toUpperCase(),
		status: (btn.dataset.staffStatus || 'ACTIVE').toUpperCase(),
		isPrimary: btn.dataset.primaryAdmin === '1' ? '1' : '0'
	};
	const action = btn.dataset.action || 'edit';
	closeStaffActionMenus();
	openManageStaffModal(payload, action);
}

function bindStaffActionMenus(){
	if (staffActionMenusBound) return;
	staffActionMenusBound = true;
	document.addEventListener('click', function(e){
		const trigger = e.target.closest('.staff-action-trigger');
		if (trigger) {
			e.preventDefault();
			const expanded = trigger.getAttribute('aria-expanded') === 'true';
			closeStaffActionMenus();
			if (!expanded) {
				const menu = trigger.nextElementSibling;
				if (menu) {
					menu.classList.add('show');
					trigger.setAttribute('aria-expanded', 'true');
				}
			}
			return;
		}
		const actionBtn = e.target.closest('.staff-action-btn');
		if (actionBtn) {
			e.preventDefault();
			handleStaffAction(actionBtn);
			return;
		}
		if (!e.target.closest('.staff-action-menu')) {
			closeStaffActionMenus();
		}
	});
}

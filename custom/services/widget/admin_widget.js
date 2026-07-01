(function () {
	'use strict';

	var WIDGET_ID = 'ec_chatbot_widget';
	var STYLE_ID = 'ec_chatbot_widget_styles';
	var rawConfig = window.ECChatbotWidgetConfig || {};
	var config = normalizeConfig(rawConfig);

	var defaultData = {
		conversations: [],
		messages: []
	};

	var state = {
		isOpen: false,
		selectedConversationId: null,
		drafts: {},
		selectedImage: null,
		subWs: null,
		subReconnectTimer: null,
		subReconnectAttempt: 0,
		lastSubscriberActivityAt: 0,
		conversationSockets: {},
		conversationReconnectTimers: {},
		conversationReconnectAttempts: {},
		conversationActivityAt: {},
		joinedConversations: {},
		realtimeWatchdogTimer: null,
		lastRecoveryAt: 0,
		realtimeReconnectingByKey: {},
		realtimeReconnectedByKey: {},
		realtimeReconnectHideTimers: {},
		typingTimer: null,
		typingDebounce: null,
		typingByConversation: {},
		typingTimersByConversation: {},
		isComposing: false,
		sendLocked: false,
		isLoadingConversations: false,
		hasLoadedInitialConversations: false,
		loadingMessagesByConversation: {},
		loadingOlderMessagesByConversation: {},
		loadedMessagesByConversation: {},
		messageNextCursorByConversation: {},
		exhaustedOlderMessagesByConversation: {},
		olderMessageLoadRequestedAt: {},
		threadRenderKey: '',
		seenByConversation: {},
		adminParticipantsByConversation: {},
		isAdminParticipantsOpen: false,
		conversationSearch: '',
		connectionStatus: '',
		customerReplyEngaged: false,
		customerWindowFocused: document.hasFocus(),
		customerSeenObserver: null,
		lastCustomerSeenMessageId: null,
		sessionId: getSessionId(),
		expandedReceiptId: null,
		pendingMessageTimers: {},
		pendingReceiptsByConversation: {}
	};

	var data = mergeData(defaultData, config);

	function getThemeColor(name, fallback) {
		try {
			var value = window.getComputedStyle(document.documentElement).getPropertyValue(name);
			return value && value.trim() ? value.trim() : fallback;
		} catch (e) {
			return fallback;
		}
	}

	function normalizeConfig(custom) {
		custom = custom || {};
		var role = custom.role === 'customer' ? 'customer' : 'admin';
		var customer = custom.customer || {};
		var conversationId = custom.conversationId || customer.conversationId || customer.id || '';

		return {
			role: role,
			title: custom.title || 'Support Chat',
			subtitle: custom.subtitle || (role === 'customer' ? 'Online' : 'Hội thoại khách hàng'),
			status: custom.status || (role === 'customer' ? 'Online' : (custom.wsUrl ? 'Đang kết nối' : 'Chưa kết nối')),
			primaryColor: custom.primaryColor || getThemeColor('--primary-color', '#0a58ca'),
			avatarUrl: custom.avatarUrl || '',
			agentAvatarUrl: custom.agentAvatarUrl || custom.avatarUrl || '',
			adminAvatarUrl: custom.adminAvatarUrl || '',
			canUploadImages: typeof custom.canUploadImages === 'boolean' ? custom.canUploadImages : role === 'admin',
			wsUrl: custom.wsUrl || '',
			apiUrl: custom.apiUrl || custom.restUrl || getUrlParam('restUrl') || getDefaultApiUrl(custom.wsUrl || getUrlParam('ws') || ''),
			apiKey: custom.apiKey || custom.restKey || custom.restApiKey || getUrlParam('restKey') || getUrlParam('apiKey') || '',
			initialConversationLimit: Number(custom.initialConversationLimit || 15),
			initialMessageLimit: Number(custom.initialMessageLimit || 15),
			apiTimeoutMs: Number(custom.apiTimeoutMs || 8000),
			apiRetryCount: Number(custom.apiRetryCount || 2),
			realtimeReconnectMaxDelayMs: Number(custom.realtimeReconnectMaxDelayMs || 15000),
			realtimeWatchdogIntervalMs: Number(custom.realtimeWatchdogIntervalMs || 10000),
			realtimeStaleMs: Number(custom.realtimeStaleMs || 45000),
			realtimeRecoveryCooldownMs: Number(custom.realtimeRecoveryCooldownMs || 5000),
			messageAckTimeoutMs: Number(custom.messageAckTimeoutMs || 10000),
			autoConnect: custom.autoConnect !== false,
			debug: !!custom.debug,
			adminId: custom.adminId || '',
			adminName: custom.adminName || '',
			conversationId: conversationId,
			customer: {
				id: conversationId,
				customerName: customer.customerName || customer.name || custom.customerName || 'Khách hàng',
				phone: customer.phone || custom.phone || '',
				avatarUrl: customer.avatarUrl || custom.customerAvatarUrl || ''
			},
			conversations: custom.conversations,
			messages: custom.messages
		};
	}

	function mergeData(base, custom) {
		if (custom.role === 'customer') {
			var conversation = Object.assign({
				id: custom.conversationId,
				customerName: custom.customer.customerName,
				phone: custom.customer.phone,
				lastMessage: '',
				unreadCount: 0,
				updatedAt: ''
			}, custom.customer || {});

			conversation.id = conversation.id || custom.conversationId;
			conversation.customerName = conversation.customerName || 'Khách hàng';
			return {
				conversations: [conversation],
				messages: custom.messages || []
			};
		}

		return {
			conversations: custom.conversations || (custom.wsUrl ? [] : base.conversations.map(function (item) {
				return Object.assign({}, item);
			})),
			messages: custom.messages || (custom.wsUrl ? [] : base.messages.map(function (item) {
				return Object.assign({}, item);
			}))
		};
	}

	function isCustomerMode() {
		return config.role === 'customer';
	}

	function canUploadImages() {
		return !!config.canUploadImages;
	}

	function getDefaultAvatarDataUri() {
		var svg = '<svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 80 80"><rect width="80" height="80" rx="40" fill="' + config.primaryColor + '"/><path fill="white" d="M22 34c0-8.3 7.7-15 18-15s18 6.7 18 15-7.7 15-18 15c-2 0-4-.3-5.8-.8L25 54l2.4-8.1C24 42.8 22 38.6 22 34Z"/><circle cx="32" cy="34" r="3" fill="' + config.primaryColor + '"/><circle cx="40" cy="34" r="3" fill="' + config.primaryColor + '"/><circle cx="48" cy="34" r="3" fill="' + config.primaryColor + '"/></svg>';
		return 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(svg);
	}

	function isGenericAdminName(name) {
		var normalized = String(name || '').trim().toLowerCase();
		return !normalized || normalized === 'admin' || normalized === 'tư vấn viên';
	}

	function normalizeStaffDisplayName(name) {
		var text = String(name || '').trim();
		return isGenericAdminName(text) ? 'admin' : text;
	}

	function getStaffDisplayName(message) {
		message = message || {};
		return normalizeStaffDisplayName(message.adminName || message.senderName);
	}

	function getMessageView(message) {
		var senderType = message.senderType || 'customer';
		var isOwn = isOwnMessage(message);
		var visualType = senderType === 'staff'
			? (isCustomerMode() ? 'agent' : 'staff')
			: (isOwn ? 'own' : senderType);
		var staffLabel = isCustomerMode() ? 'Nhân viên' : getStaffDisplayName(message);
		var customerConversation = senderType === 'customer' ? getConversation(message.conversationId) : null;
		var customerColors = senderType === 'customer'
			? getAvatarColor(getCustomerAvatarColorKey(customerConversation) || message.senderName || message.conversationId || 'Khách hàng')
			: null;

		return {
			visualType: visualType,
			label: senderType === 'customer' ? (isCustomerMode() ? 'Bạn' : 'Khách') : (senderType === 'ai' ? 'AI Bot' : staffLabel),
			avatarText: senderType === 'customer' ? 'KH' : (senderType === 'ai' ? 'AI' : 'NV'),
			avatarUrl: isOwn ? '' : (isCustomerMode() ? (config.agentAvatarUrl || config.avatarUrl || getDefaultAvatarDataUri()) : ''),
			avatarBg: isOwn ? config.primaryColor : (senderType === 'customer' ? customerColors.bg : (senderType === 'ai' ? '#bfdbfe' : '#f1f5f9')),
			avatarColor: isOwn ? '#fff' : (senderType === 'customer' ? customerColors.text : (senderType === 'ai' ? '#1d4ed8' : '#64748b'))
		};
	}

	function escapeHtml(value) {
		return String(value || '')
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;')
			.replace(/'/g, '&#039;');
	}

	function toPlainText(value) {
		if (value === undefined || value === null) return '';
		return String(value)
			.replace(/\u0000/g, '')
			.replace(/\r\n/g, '\n')
			.replace(/\r/g, '\n');
	}

	function truncateText(value, maxLength) {
		var text = String(value || '').replace(/\s+/g, ' ').trim();
		if (text.length <= maxLength) {
			return text;
		}
		return text.substring(0, Math.max(0, maxLength - 3)).trim() + '...';
	}

	function formatCurrentTime() {
		var now = new Date();
		return now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: false });
	}

	// ── Session ID: unique per browser tab, lives in sessionStorage ─────────────
	// Used for echo suppression: admin sees own message immediately, server
	// echoes it back with this sessionId → skip re-render.
	function getSessionId() {
		try {
			var storage = getSessionStorage();
			var existing = storage && storage.getItem('ec_cw_session_id');
			if (existing) return existing;
			var id = (window.crypto && crypto.randomUUID) ? crypto.randomUUID() : (Math.random().toString(36).slice(2) + Date.now().toString(36));
			if (storage) storage.setItem('ec_cw_session_id', id);
			return id;
		} catch (e) {
			return Math.random().toString(36).slice(2);
		}
	}


	function createClientMessageId() {
		if (window.crypto && crypto.randomUUID) return crypto.randomUUID();
		return 'msg_' + Date.now() + '_' + Math.random().toString(36).slice(2);
	}

	function isRealtimeEnabled() {
		return !!(config.wsUrl && window.WebSocket);
	}

	function getDefaultApiUrl(wsUrl) {
		var url = String(wsUrl || '').trim();
		if (!url) return '';
		return url.replace(/^ws(s?):\/\//, 'http$1://').replace(/\/$/, '');
	}

	function getUrlParam(name) {
		try {
			return new URLSearchParams(window.location.search).get(name) || '';
		} catch (e) {
			return '';
		}
	}

	function getSessionStorage() {
		try {
			var storage = window.sessionStorage;
			var testKey = '__ec_cw_storage_test__';
			storage.setItem(testKey, '1');
			storage.removeItem(testKey);
			return storage;
		} catch (e) {
			return null;
		}
	}

	function getAdminName() {
		var pageContent = qs('#pagecontent');
		return config.adminName || (pageContent && pageContent.getAttribute('data-current-user')) || 'Tư vấn viên';
	}

	function getAdminId() {
		var pageContent = qs('#pagecontent');
		return config.adminId || (pageContent && pageContent.getAttribute('data-current-user')) || state.sessionId;
	}

	// ── conversationId is now a UUID; phone is carried separately ───────────────
	// `formatConversationPhone` used to strip "phone_" prefix — now we read the
	// phone field that the server includes in `notify` and on the conversation entry.
	function getConversationPhone(conversation) {
		if (!conversation) return '';
		return String(conversation.phone || '').trim();
	}

	function getCustomerAvatarColorKey(conversation) {
		if (!conversation) return 'customer:unknown';
		var phone = normalizePhoneLike(conversation.phone || conversation.id || '');
		return 'customer:' + (phone || String(conversation.customerName || conversation.id || 'unknown').trim().toLowerCase());
	}

	function getAdminAvatarColorKey(value) {
		return 'admin:' + String(value || 'unknown').trim().toLowerCase();
	}

	function getAdminParticipantKey(admin) {
		if (!admin) return 'unknown';
		var id = admin.adminId || admin.id || '';
		if (id) return 'id:' + String(id).trim().toLowerCase();
		var name = admin.adminName || admin.senderName || admin.name || admin.label || '';
		return 'name:' + String(name || 'unknown').trim().toLowerCase();
	}

	function setAdminParticipant(participantsByName, admin) {
		if (!participantsByName || !admin) return;
		var name = admin.adminName || admin.senderName || admin.name || admin.label || '';
		if (!name) return;
		var key = getAdminParticipantKey(admin);
		var existing = participantsByName[key];
		if (existing) {
			if (existing.label === existing.label.toLowerCase() && name !== name.toLowerCase()) {
				existing.label = name;
				existing.initials = getAdminInitials(name, 'NV');
			}
			existing.avatarUrl = existing.avatarUrl || admin.adminAvatarUrl || admin.avatarUrl || '';
			return;
		}
		participantsByName[key] = {
			label: name,
			initials: getAdminInitials(name, 'NV'),
			avatarUrl: admin.adminAvatarUrl || admin.avatarUrl || '',
			colorKey: getAdminAvatarColorKey(key)
		};
	}

	function normalizeServerTimestamp(value) {
		if (typeof value !== 'string') return value;
		var text = value.trim();
		if (/^\d{10,13}$/.test(text)) {
			var numeric = Number(text);
			return text.length === 10 ? numeric * 1000 : numeric;
		}
		if (!text || /^\d{1,2}:\d{2}(:\d{2})?$/.test(text)) return text;
		if (!/^\d{4}-\d{2}-\d{2}[ T]\d{2}:\d{2}(:\d{2})?/.test(text)) return text;
		if (/[zZ]$|[+-]\d{2}:?\d{2}$/.test(text)) return text.replace(' ', 'T');
		return text.replace(' ', 'T') + 'Z';
	}

	function formatTimestamp(timestamp) {
		if (!timestamp) return '';
		if (typeof timestamp === 'string') {
			var text = timestamp.trim();
			if (/^\d{1,2}:\d{2}(:\d{2})?$/.test(text)) {
				return text.substring(0, 5);
			}
			timestamp = normalizeServerTimestamp(text);
		}
		var date = new Date(timestamp);
		if (isNaN(date.getTime())) return '';
		return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: false });
	}

	function parseDateTime(value) {
		if (!value) return null;
		if (value instanceof Date) return isNaN(value.getTime()) ? null : value;
		if (typeof value === 'number') {
			var numericDate = new Date(value);
			return isNaN(numericDate.getTime()) ? null : numericDate;
		}
		var text = String(value || '').trim();
		if (!text || /^\d{1,2}:\d{2}(:\d{2})?$/.test(text)) return null;
		var date = new Date(normalizeServerTimestamp(text));
		return isNaN(date.getTime()) ? null : date;
	}

	function startOfDay(date) {
		return new Date(date.getFullYear(), date.getMonth(), date.getDate()).getTime();
	}

	function formatTelegramListTime(rawValue, fallbackValue) {
		var fallback = String(fallbackValue || '').trim();
		var date = parseDateTime(rawValue) || parseDateTime(fallback);
		if (!date) {
			return /^\d{1,2}:\d{2}(:\d{2})?$/.test(fallback) ? fallback.substring(0, 5) : fallback;
		}

		var now = new Date();
		var diffDays = Math.floor((startOfDay(now) - startOfDay(date)) / 86400000);
		if (diffDays <= 0) {
			return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: false });
		}
		if (diffDays === 1) return 'Hôm qua';
		if (diffDays < 7) {
			return ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'][date.getDay()];
		}
		var day = String(date.getDate()).padStart(2, '0');
		var month = String(date.getMonth() + 1).padStart(2, '0');
		var year = String(date.getFullYear()).slice(-2);
		return day + '/' + month + '/' + year;
	}

	function getMessageDate(message) {
		if (!message) return null;
		return parseDateTime(message.createdAtRaw || message.timestamp || '');
	}

	function getMessageDateKey(message) {
		var date = getMessageDate(message);
		if (!date) return '';
		var month = String(date.getMonth() + 1).padStart(2, '0');
		var day = String(date.getDate()).padStart(2, '0');
		return date.getFullYear() + '-' + month + '-' + day;
	}

	function formatMessageDateDivider(message) {
		var date = getMessageDate(message);
		if (!date) return '';
		var now = new Date();
		var diffDays = Math.floor((startOfDay(now) - startOfDay(date)) / 86400000);
		if (diffDays === 0) return 'Hôm nay';
		if (diffDays === 1) return 'Hôm qua';
		var months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
		return String(date.getDate()).padStart(2, '0') + ' ' + months[date.getMonth()];
	}

	function isTodayMessageDate(message) {
		var date = getMessageDate(message);
		if (!date) return false;
		return startOfDay(date) === startOfDay(new Date());
	}

	function renderDateDivider(message) {
		var label = formatMessageDateDivider(message);
		var dateKey = getMessageDateKey(message);
		return label ? '<div class="ec-cw__date-divider" data-date-key="' + escapeHtml(dateKey) + '">' + escapeHtml(label) + '</div>' : '';
	}

	function renderMessageThread(messages) {
		var lastDateKey = '';
		return (messages || []).map(function (message, index) {
			var dateKey = getMessageDateKey(message);
			var shouldRenderDivider = dateKey && dateKey !== lastDateKey && (lastDateKey || !isTodayMessageDate(message));
			var divider = shouldRenderDivider ? renderDateDivider(message) : '';
			if (dateKey) lastDateKey = dateKey;
			return divider + renderMessage(message, false, shouldHideMessageMeta(message, index, messages));
		}).join('');
	}

	function getThreeInitials(name, fallback) {
		var text = String(name || '').trim();
		if (!text) return fallback || '?';
		return text.replace(/\s+/g, '').substring(0, 3).toUpperCase();
	}

	function getAdminInitials(name, fallback) {
		var text = String(name || '').trim();
		if (!text) return fallback || 'NV';
		return text.replace(/\s+/g, '').substring(0, 2).toUpperCase();
	}

	function getCustomerAvatarText() {
		return 'KH';
	}

	function renderSmallAvatar(person, extraClass) {
		var label = person && person.label ? person.label : '';
		var image = person && person.avatarUrl ? person.avatarUrl : '';
		var initials = person && person.initials ? person.initials : getThreeInitials(label, '?');
		var colors = getAvatarColor((person && person.colorKey) || label || initials);
		var className = 'ec-cw__header-avatar-chip' + (extraClass ? ' ' + extraClass : '');
		if (image) {
			return '<span class="' + className + '"><img src="' + escapeHtml(image) + '" alt="' + escapeHtml(label || initials) + '"></span>';
		}
		return '<span class="' + className + '" style="background:' + colors.bg + ';color:' + colors.text + '">' + escapeHtml(initials) + '</span>';
	}

	function renderParticipantAvatar(person, extraClass) {
		var label = person && person.label ? person.label : '';
		var image = person && person.avatarUrl ? person.avatarUrl : '';
		var initials = person && person.initials ? person.initials : getThreeInitials(label, '?');
		var colors = getAvatarColor((person && person.colorKey) || label || initials);
		var className = 'ec-cw__participant-avatar' + (extraClass ? ' ' + extraClass : '');
		if (image) {
			return '<span class="' + className + '"><img src="' + escapeHtml(image) + '" alt="' + escapeHtml(label || initials) + '"></span>';
		}
		return '<span class="' + className + '" style="background:' + colors.bg + ';color:' + colors.text + '">' + escapeHtml(initials) + '</span>';
	}

	function getCustomerHeaderParticipant(conversation) {
		if (!conversation) {
			return null;
		}
		return {
			label: conversation.customerName || conversation.phone || 'Khách hàng',
			initials: getCustomerAvatarText(),
			avatarUrl: conversation.avatarUrl || conversation.customerAvatarUrl || '',
			colorKey: getCustomerAvatarColorKey(conversation)
		};
	}

	function rememberAdminParticipant(conversationId, admin) {
		if (!conversationId || !admin) return;
		var name = admin.adminName || admin.senderName || admin.name || '';
		if (!name) return;
		if (!state.adminParticipantsByConversation[conversationId]) {
			state.adminParticipantsByConversation[conversationId] = {};
		}
		setAdminParticipant(state.adminParticipantsByConversation[conversationId], admin);
	}

	function setLiveAdminParticipants(conversationId, admins) {
		if (!conversationId) return;
		state.adminParticipantsByConversation[conversationId] = {};
		(admins || []).forEach(function (admin) {
			rememberAdminParticipant(conversationId, admin);
		});
	}

	function removeAdminParticipant(conversationId, admin) {
		if (!conversationId || !admin || !state.adminParticipantsByConversation[conversationId]) return;
		delete state.adminParticipantsByConversation[conversationId][getAdminParticipantKey(admin)];
	}

	function getHeaderAdminParticipants(conversation) {
		if (!conversation || isCustomerMode()) return [];
		var participantsByName = Object.assign({}, state.adminParticipantsByConversation[conversation.id] || {});

		return Object.keys(participantsByName).map(function (name) {
			return participantsByName[name];
		}).slice(0, 6);
	}

	function getHeaderTitle(conversation) {
		if (isCustomerMode()) return config.title;
		if (!conversation) return 'Hội thoại';
		// Show phone number if available, otherwise customerName
		var phone = getConversationPhone(conversation);
		return phone || conversation.customerName || 'Khách hàng';
	}

	function getHeaderStatus(conversation) {
		if (!isCustomerMode() && !conversation) return 'Online';
		if (!isCustomerMode() && conversation) return conversation.peerOnline ? 'online' : 'offline';
		return state.connectionStatus || config.status || config.subtitle;
	}

	function getHeaderPresenceClass(conversation) {
		if (isCustomerMode() || !conversation) return ' is-online';
		return conversation.peerOnline ? ' is-online' : ' is-offline';
	}

	function renderAdminParticipantsPopover(admins) {
		if (!admins || !admins.length) return '<div class="ec-cw__admin-participants-popover"></div>';
		var openClass = state.isAdminParticipantsOpen ? ' is-open' : '';
		var rows = admins.map(function (admin) {
			return [
				'<div class="ec-cw__participant-row">',
				renderParticipantAvatar(admin),
				'<div class="ec-cw__participant-info">',
				'<div class="ec-cw__participant-name">' + escapeHtml(admin.label || 'Tư vấn viên') + '</div>',
				'<div class="ec-cw__participant-role">Đang tham gia</div>',
				'</div>',
				'</div>'
			].join('');
		}).join('');

		return [
			'<div class="ec-cw__admin-participants-popover' + openClass + '">',
			'<div class="ec-cw__participant-title">Tư vấn viên đang tham gia</div>',
			rows,
			'</div>'
		].join('');
	}

	function updateHeader(conversation) {
		var root = document.getElementById(WIDGET_ID);
		if (!root) return;
		var title = qs('.ec-cw__header-title', root);
		var subtitle = qs('.ec-cw__header-subtitle', root);
		var customerAvatar = qs('.ec-cw__header-customer-avatar', root);
		var adminAvatars = qs('.ec-cw__header-admin-avatars', root);
		var adminPopover = qs('.ec-cw__admin-participants-popover', root);
		var admins = getHeaderAdminParticipants(conversation);
		if (!admins.length) state.isAdminParticipantsOpen = false;
		if (title) title.textContent = getHeaderTitle(conversation);
		if (subtitle) {
			subtitle.className = 'ec-cw__header-subtitle' + getHeaderPresenceClass(conversation);
			subtitle.innerHTML = '<span class="ec-cw__header-status-text">' + escapeHtml(getHeaderStatus(conversation)) + '</span><span class="ec-cw__header-admin-avatars" title="Xem admin đang tham gia"></span>';
			adminAvatars = qs('.ec-cw__header-admin-avatars', root);
		}
		if (customerAvatar) {
			var customerParticipant = getCustomerHeaderParticipant(conversation);
			customerAvatar.innerHTML = customerParticipant
				? renderSmallAvatar(customerParticipant, 'ec-cw__header-avatar-chip--customer')
				: '';
		}
		if (adminAvatars) {
			adminAvatars.innerHTML = admins.map(function (admin) {
				return renderSmallAvatar(admin, 'ec-cw__header-avatar-chip--admin');
			}).join('');
		}
		adminPopover = qs('.ec-cw__admin-participants-popover', root);
		if (adminPopover) {
			adminPopover.outerHTML = renderAdminParticipantsPopover(admins);
		}
	}

	function roleToSenderType(role) {
		if (role === 'admin') return 'staff';
		if (role === 'customer') return 'customer';
		return role || 'system';
	}

	function debugLog() {
		if (!config.debug || !window.console) return;
		// console.log.apply(console, ['[ECChatbotWidget]'].concat(Array.prototype.slice.call(arguments)));
	}

	function qs(selector, root) {
		return (root || document).querySelector(selector);
	}

	function qsa(selector, root) {
		return Array.prototype.slice.call((root || document).querySelectorAll(selector));
	}

	function getConversation(id) {
		var exact = data.conversations.find(function (conversation) {
			return conversation.id === id;
		});
		if (exact) return exact;
		var phone = normalizePhoneLike(id);
		if (!phone) return null;
		return getConversationByPhone(phone) || null;
	}

	function normalizePhone(value) {
		return String(value || '').replace(/\D/g, '');
	}

	function normalizePhoneLike(value) {
		var text = String(value || '').trim();
		if (!text) return '';
		if (/^phone_/i.test(text)) return normalizePhone(text);
		if (!/^[+\d\s().-]+$/.test(text)) return '';
		var phone = normalizePhone(text);
		return phone.length >= 7 && phone.length <= 15 ? phone : '';
	}

	// Extracts leading phone digits from any convId format:
	//   "0901234567"              → "0901234567"  (old bare-phone format)
	//   "0901234567_m2kzr4_ab3f" → "0901234567"  (new unique format)
	//   "abc-uuid-123"           → ""             (non-phone convId, safe no-op)
	function extractPhoneFromConvId(convId) {
		var m = String(convId || '').match(/^(\d{7,15})(?:_|$)/);
		return m ? m[1] : '';
	}

	// Resolves the phone for a conversation regardless of where the ID came from.
	function resolveConversationPhone(conversation, convIdFallback) {
		return normalizePhoneLike(getConversationPhone(conversation))
			|| extractPhoneFromConvId(convIdFallback || (conversation && conversation.id) || '');
	}

	function getCanonicalConversationId(value, fallback) {
		var phone = normalizePhoneLike(value);
		return phone || String(value || fallback || '').trim();
	}

	function getConversationByPhone(phone) {
		var normalized = normalizePhoneLike(phone);
		if (!normalized) return null;
		return data.conversations.find(function (conversation) {
			return normalizePhoneLike(conversation.phone) === normalized || normalizePhoneLike(conversation.id) === normalized;
		});
	}

	function isSameConversationId(a, b) {
		if (a === b) return true;
		var aPhone = normalizePhoneLike(a);
		var bPhone = normalizePhoneLike(b);
		return !!(aPhone && bPhone && aPhone === bPhone);
	}

	function moveConversationReferences(oldId, newId) {
		if (!oldId || !newId || oldId === newId) return;
		data.messages.forEach(function (message) {
			if (isSameConversationId(message.conversationId, oldId)) message.conversationId = newId;
		});
		if (state.selectedConversationId === oldId) state.selectedConversationId = newId;
		if (state.drafts[oldId] && !state.drafts[newId]) state.drafts[newId] = state.drafts[oldId];
		delete state.drafts[oldId];
		if (state.adminParticipantsByConversation[oldId]) {
			state.adminParticipantsByConversation[newId] = Object.assign(
				state.adminParticipantsByConversation[newId] || {},
				state.adminParticipantsByConversation[oldId]
			);
			delete state.adminParticipantsByConversation[oldId];
		}
	}

	function mergeConversationInto(target, source) {
		if (!target || !source || target === source) return target;
		moveConversationReferences(source.id, target.id);
		target.unreadCount = Number(target.unreadCount || 0) + Number(source.unreadCount || 0);
		if (source.lastMessage && shouldUseConversationPreview(target, source)) {
			target.lastMessage = source.lastMessage;
			target.lastMessageSenderType = source.lastMessageSenderType || target.lastMessageSenderType || '';
			target.lastMessageAdminName = source.lastMessageAdminName || target.lastMessageAdminName || '';
			target.updatedAt = source.updatedAt || target.updatedAt || '';
			target.updatedAtRaw = source.updatedAtRaw || target.updatedAtRaw || '';
		}
		data.conversations = data.conversations.filter(function (conversation) {
			return conversation !== source;
		});
		return target;
	}

	function getMessages(conversationId) {
		var phone = normalizePhoneLike(conversationId);
		return data.messages.filter(function (message) {
			return message.conversationId === conversationId || (phone && normalizePhoneLike(message.conversationId) === phone);
		}).sort(compareMessagesByTime);
	}

	function getMessageTimeMs(message) {
		if (!message) return null;
		var raw = message.createdAtRaw || message.timestamp || '';
		var parsed = parseDateTime(raw);
		if (parsed) return parsed.getTime();
		var text = String(message.createdAt || '').trim();
		if (/^\d{1,2}:\d{2}(:\d{2})?$/.test(text)) {
			var parts = text.split(':');
			var today = new Date();
			today.setHours(Number(parts[0] || 0), Number(parts[1] || 0), Number(parts[2] || 0), 0);
			return today.getTime();
		}
		return null;
	}

	function getConversationTimeMs(conversation) {
		if (!conversation) return null;
		var parsed = parseDateTime(conversation.updatedAtRaw || conversation.updatedAt || '');
		return parsed ? parsed.getTime() : null;
	}

	function getConversationSortTimeMs(conversation) {
		var latest = conversation && getLatestNonSystemMessage(conversation.id);
		var latestTime = getMessageTimeMs(latest);
		if (latestTime !== null) return latestTime;
		var conversationTime = getConversationTimeMs(conversation);
		return conversationTime !== null ? conversationTime : 0;
	}

	function compareConversationsByActivity(a, b) {
		var diff = getConversationSortTimeMs(b) - getConversationSortTimeMs(a);
		if (diff) return diff;
		return String((b && b.id) || '').localeCompare(String((a && a.id) || ''));
	}

	function sortConversationsByActivity(conversations) {
		return (conversations || []).slice().sort(compareConversationsByActivity);
	}

	function shouldUseConversationPreview(current, incoming) {
		if (!incoming || !incoming.lastMessage) return false;
		if (!current || !current.lastMessage) return true;
		var currentTime = getConversationTimeMs(current);
		var incomingTime = getConversationTimeMs(incoming);
		if (currentTime === null) return true;
		if (incomingTime === null) return false;
		return incomingTime >= currentTime;
	}

	function compareMessagesByTime(a, b) {
		var aTime = getMessageTimeMs(a);
		var bTime = getMessageTimeMs(b);
		if (aTime !== null && bTime !== null && aTime !== bTime) return aTime - bTime;
		var aRaw = String((a && (a.createdAtRaw || a.createdAt || a.timestamp)) || '');
		var bRaw = String((b && (b.createdAtRaw || b.createdAt || b.timestamp)) || '');
		if (aRaw !== bRaw) return aRaw.localeCompare(bRaw);
		return String((a && a.id) || '').localeCompare(String((b && b.id) || ''));
	}

	function compareMessagesByConversationAndTime(a, b) {
		var aConversation = String((a && a.conversationId) || '');
		var bConversation = String((b && b.conversationId) || '');
		if (aConversation !== bConversation) return aConversation.localeCompare(bConversation);
		return compareMessagesByTime(a, b);
	}

	function getMessageRenderToken(message) {
		if (!message) return '';
		return [
			message.id || '',
			message.senderType || '',
			message.adminId || '',
			message.adminName || '',
			message.content || message.text || '',
			message.imageUrl || '',
			message.createdAtRaw || message.createdAt || message.timestamp || '',
			message.seenAtRaw || message.seenAt || '',
			message.deliveredAtRaw || message.deliveredAt || '',
			message.delivered ? '1' : '0',
			message.deliveryState || ''
		].join('|');
	}

	function getThreadRenderKey(conversationId, messages) {
		return [
			conversationId || '',
			(messages || []).map(getMessageRenderToken).join('~')
		].join('::');
	}

	function getDeliveryStateRank(stateValue) {
		if (stateValue === 'failed') return 0;
		if (stateValue === 'sending') return 1;
		if (stateValue === 'sent') return 2;
		return -1;
	}

	function preserveReceiptState(existing, incoming, previous) {
		if (!existing || !incoming) return;
		previous = previous || {};
		var previousSeenAt = previous.seenAt || '';
		var previousSeenAtRaw = previous.seenAtRaw || '';
		var previousDeliveredAt = previous.deliveredAt || '';
		var previousDeliveredAtRaw = previous.deliveredAtRaw || '';
		var previousReceivedAt = previous.receivedAt || '';
		var previousReceivedAtRaw = previous.receivedAtRaw || '';
		var previousDelivered = !!previous.delivered;
		var previousDeliveryState = previous.deliveryState || '';

		if (!incoming.seenAt && previousSeenAt) existing.seenAt = previousSeenAt;
		if (!incoming.seenAtRaw && previousSeenAtRaw) existing.seenAtRaw = previousSeenAtRaw;
		if (!incoming.deliveredAt && previousDeliveredAt) existing.deliveredAt = previousDeliveredAt;
		if (!incoming.deliveredAtRaw && previousDeliveredAtRaw) existing.deliveredAtRaw = previousDeliveredAtRaw;
		if (!incoming.receivedAt && previousReceivedAt) existing.receivedAt = previousReceivedAt;
		if (!incoming.receivedAtRaw && previousReceivedAtRaw) existing.receivedAtRaw = previousReceivedAtRaw;
		if (!incoming.delivered && previousDelivered && (existing.deliveredAt || existing.receivedAt)) existing.delivered = true;
		if (previousDeliveryState && getDeliveryStateRank(previousDeliveryState) > getDeliveryStateRank(existing.deliveryState)) {
			existing.deliveryState = previousDeliveryState;
		}
	}

	function findDuplicateMessage(message) {
		if (!message) return null;
		if (message.id) {
			for (var i = 0; i < data.messages.length; i++) {
				if (data.messages[i].id === message.id) return data.messages[i];
			}
		}

		var incomingStableId = getStableMessageId(message);
		var messageTime = getMessageTimeMs(message);
		var content = String(message.content || message.text || '').trim();
		var imageUrl = String(message.imageUrl || '').trim();
		if (!content && !imageUrl) return null;

		for (var j = 0; j < data.messages.length; j++) {
			var existing = data.messages[j];
			var existingStableId = getStableMessageId(existing);
			if (incomingStableId && existingStableId && incomingStableId !== existingStableId) continue;
			if (!isSameConversationId(existing.conversationId, message.conversationId)) continue;
			if ((existing.senderType || 'customer') !== (message.senderType || 'customer')) continue;
			if (!isSameDuplicateActor(existing, message)) continue;
			if (String(existing.content || existing.text || '').trim() !== content) continue;
			if (String(existing.imageUrl || '').trim() !== imageUrl) continue;

			var existingTime = getMessageTimeMs(existing);
			if (messageTime !== null && existingTime !== null) {
				if (Math.abs(existingTime - messageTime) <= 90000) return existing;
				continue;
			}
			if (String(existing.createdAt || '') && String(existing.createdAt || '') === String(message.createdAt || '')) {
				return existing;
			}
		}

		return null;
	}

	function getStableMessageId(message) {
		var id = String((message && message.id) || '').trim();
		if (!id || /^api_/i.test(id)) return '';
		return id;
	}

	function isSameDuplicateActor(existing, incoming) {
		var senderType = incoming && incoming.senderType ? incoming.senderType : 'customer';
		if (senderType !== 'staff') return true;
		var existingKey = getStaffDuplicateActorKey(existing);
		var incomingKey = getStaffDuplicateActorKey(incoming);
		if (!existingKey || !incomingKey) return false;
		return existingKey === incomingKey;
	}

	function getStaffDuplicateActorKey(message) {
		if (!message) return '';
		if (message.sessionId) return 'session:' + String(message.sessionId);
		if (message.adminId) return 'id:' + String(message.adminId);
		var name = message.adminName || message.senderName || '';
		if (isGenericAdminName(name)) return '';
		return 'name:' + String(name).trim().toLowerCase();
	}

	function upsertMessage(message) {
		var existing = findDuplicateMessage(message);
		if (existing) {
			var existingAdminName = existing.adminName || existing.senderName || '';
			var existingAdminId = existing.adminId || '';
			var existingSessionId = existing.sessionId || '';
			var incomingAdminName = message.adminName || message.senderName || '';
			var previousReceiptState = {
				seenAt: existing.seenAt || '',
				seenAtRaw: existing.seenAtRaw || '',
				deliveredAt: existing.deliveredAt || '',
				deliveredAtRaw: existing.deliveredAtRaw || '',
				receivedAt: existing.receivedAt || '',
				receivedAtRaw: existing.receivedAtRaw || '',
				delivered: !!existing.delivered,
				deliveryState: existing.deliveryState || ''
			};
			Object.assign(existing, message);
			preserveReceiptState(existing, message, previousReceiptState);
			if (message.senderType === 'staff' && !isGenericAdminName(existingAdminName) && isGenericAdminName(incomingAdminName)) {
				existing.adminName = existingAdminName;
				existing.senderName = existing.senderName || existingAdminName;
			}
			if (message.senderType === 'staff') {
				existing.adminId = message.adminId || existingAdminId || '';
				existing.sessionId = message.sessionId || existingSessionId || '';
			}
			return existing;
		}
		data.messages.push(message);
		return message;
	}

	function getMessageById(messageId) {
		if (!messageId) return null;
		for (var i = 0; i < data.messages.length; i++) {
			if (data.messages[i].id === messageId) return data.messages[i];
		}
		return null;
	}

	function clearPendingMessageTimer(messageId) {
		clearTimeout(state.pendingMessageTimers[messageId]);
		delete state.pendingMessageTimers[messageId];
	}

	function refreshMessageUi(message) {
		if (!message) return;
		saveCachedConversationMessages(message.conversationId);
		if (isSameConversationId(state.selectedConversationId, message.conversationId)) {
			renderThread({ focus: true });
		}
		renderList();
	}

	function markPendingMessageSent(messageId) {
		var message = getMessageById(messageId);
		if (!message) return;
		clearPendingMessageTimer(messageId);
		message.deliveryState = 'sent';
		if (!message.deliveredAt && !message.receivedAt) message.delivered = false;
		refreshMessageUi(message);
	}

	function markPendingMessageFailed(messageId) {
		var message = getMessageById(messageId);
		if (!message || message.deliveryState !== 'sending') return;
		clearPendingMessageTimer(messageId);
		message.deliveryState = 'failed';
		refreshMessageUi(message);
	}

	function startPendingMessageTimer(message) {
		if (!message || !message.id) return;
		clearPendingMessageTimer(message.id);
		state.pendingMessageTimers[message.id] = setTimeout(function () {
			markPendingMessageFailed(message.id);
		}, config.messageAckTimeoutMs || 10000);
	}

	function sendRealtimeMessagePayload(message) {
		if (!message || !isRealtimeEnabled()) return false;
		var selectedConversationId = message.conversationId || state.selectedConversationId;
		var selectedSent = false;
		getTargetConversationIds().forEach(function (targetId) {
			var ws = state.conversationSockets[targetId];
			if (!ws || ws.readyState !== WebSocket.OPEN) {
				joinRealtimeConversation(targetId, { force: true });
				return;
			}
			var targetMsgId = targetId === selectedConversationId
				? message.id
				: createClientMessageId();
			ws.send(JSON.stringify({
				type: 'message',
				id: targetMsgId,
				conversationId: targetId,
				text: message.content || '',
				imageUrl: message.imageUrl || null,
				sessionId: state.sessionId
			}));
			if (targetId === selectedConversationId) selectedSent = true;
		});
		return selectedSent;
	}

	function retryFailedMessage(messageId) {
		var message = getMessageById(messageId);
		if (!message || message.deliveryState !== 'failed') return;
		message.deliveryState = 'sending';
		refreshMessageUi(message);
		if (!sendRealtimeMessagePayload(message)) {
			markPendingMessageFailed(message.id);
			return;
		}
		startPendingMessageTimer(message);
	}

	function isApiEnabled() {
		return !!(config.apiUrl && !isCustomerMode() && window.fetch);
	}

	function sleep(ms) {
		return new Promise(function (resolve) {
			setTimeout(resolve, ms);
		});
	}

	function getRetryDelay(attempt, maxDelay) {
		var base = Math.min(maxDelay || 15000, 500 * Math.pow(2, Math.max(0, attempt)));
		return base + Math.floor(Math.random() * 200);
	}

	function isRetryableApiError(error) {
		if (!error) return true;
		if (error.name === 'AbortError' || error.isTimeout) return true;
		if (!error.status) return true;
		return error.status >= 500 || error.status === 408 || error.status === 429;
	}

	function fetchWithTimeout(url, options, timeoutMs) {
		options = options || {};
		var controller = window.AbortController ? new AbortController() : null;
		var timeoutId = null;
		var requestOptions = Object.assign({}, options);
		if (controller) {
			requestOptions.signal = controller.signal;
			timeoutId = setTimeout(function () {
				controller.abort();
			}, timeoutMs || 8000);
		}
		return fetch(url, requestOptions).catch(function (error) {
			if (error && error.name === 'AbortError') error.isTimeout = true;
			throw error;
		}).then(function (response) {
			return response;
		}).finally(function () {
			if (timeoutId) clearTimeout(timeoutId);
		});
	}

	function fetchWithRetry(url, options, retryOptions) {
		retryOptions = retryOptions || {};
		var retries = Number(retryOptions.retries || 0);
		var timeoutMs = Number(retryOptions.timeoutMs || 8000);

		function run(attempt) {
			return fetchWithTimeout(url, options, timeoutMs).then(function (response) {
				if (!response.ok && response.status >= 500 && attempt < retries) {
					var error = new Error('Retryable API status ' + response.status);
					error.status = response.status;
					throw error;
				}
				return response;
			}).catch(function (error) {
				if (attempt >= retries || !isRetryableApiError(error)) throw error;
				debugLog('api retry', url, 'attempt', attempt + 1, error && error.message ? error.message : error);
				return sleep(getRetryDelay(attempt, 3000)).then(function () {
					return run(attempt + 1);
				});
			});
		}

		return run(0);
	}

	function callLiveChatApi(method, params) {
		if (!isApiEnabled()) return Promise.resolve(null);
		var request = buildMongoApiRequest(method, params || {});
		if (!request) return Promise.resolve({});
		return fetchWithRetry(request.url, {
			method: request.method || 'GET',
			credentials: 'same-origin',
			headers: getMongoApiHeaders()
		}, {
			timeoutMs: config.apiTimeoutMs,
			retries: config.apiRetryCount
		}).then(function (response) {
			return response.text().then(function (text) {
				var payload;
				try {
					payload = text ? JSON.parse(text) : {};
				} catch (e) {
					throw new Error('Invalid API response from ' + request.url + ': ' + String(text || '').slice(0, 120));
				}
				if (!response.ok) {
					var errorText = (payload && (payload.error || payload.message)) || 'Live chat Mongo API error';
					if (response.status === 401) errorText += ' - missing/invalid restKey';
					var apiError = new Error(errorText);
					apiError.status = response.status;
					throw apiError;
				}
				return payload || {};
			});
		});
	}

	function getMongoApiHeaders() {
		var headers = { 'Accept': 'application/json' };
		var apiKey = getMongoApiKey();
		if (apiKey) headers.Authorization = 'Bearer ' + apiKey;
		return headers;
	}

	function getMongoApiKey() {
		var configured = config.apiKey || getUrlParam('restKey') || getUrlParam('apiKey');
		if (configured) return configured;
		var input = qs('#restKeyInput');
		return input && input.value ? input.value.trim() : '';
	}

	function buildMongoApiRequest(method, params) {
		var base = getMongoApiBase(config.apiUrl);
		if (!base) return null;
		if (method === 'getConversations') {
			return {
				url: appendQuery(base + '/api/conversations', {
					limit: params.limit || config.initialConversationLimit || 15
				})
			};
		}
		if (method === 'getMessages') {
			var conversationId = params.conversation_id || params.conversationId || '';
			if (!conversationId) return null;
			return {
				url: appendQuery(base + '/api/conversations/' + encodeURIComponent(conversationId) + '/messages', {
					limit: params.limit || config.initialMessageLimit || 15,
					before: params.before
				})
			};
		}
		if (method === 'markConversationRead') {
			return null;
		}
		return null;
	}

	function getMongoApiBase(apiUrl) {
		var base = String(apiUrl || '').replace(/\/$/, '');
		if (/\/index\.php\b/i.test(base) || /entryLiveChatAdminClass/i.test(base) || /entryPointGeneral/i.test(base)) {
			base = getDefaultApiUrl(config.wsUrl || getUrlParam('ws') || '');
		}
		return base.replace(/\/api$/, '');
	}

	function appendQuery(url, params) {
		var query = Object.keys(params || {}).filter(function (key) {
			return params[key] !== undefined && params[key] !== null && params[key] !== '';
		}).map(function (key) {
			return encodeURIComponent(key) + '=' + encodeURIComponent(params[key]);
		}).join('&');
		return query ? (url + (url.indexOf('?') === -1 ? '?' : '&') + query) : url;
	}

	function normalizeApiConversation(conversation) {
		conversation = conversation || {};
		var phone = normalizePhoneLike(conversation.phone || conversation.customerPhone || conversation.client_phone || '');
		var rawId = conversation.conversationId || conversation.id || conversation._id || phone;
		var id = getCanonicalConversationId(rawId) || phone;
		if (!id) return null;
		var latestMessage = conversation.latestMessage || null;
		var latestSenderType = latestMessage
			? (latestMessage.senderType || roleToSenderType(latestMessage.role))
			: (conversation.lastMessageSenderType || '');
		return {
			id: id,
			customerName: conversation.customerName || conversation.customer_name || phone || id,
			phone: phone,
			lastMessage: toPlainText(conversation.lastMessage || ''),
			lastMessageSenderType: conversation.lastMessageSenderType || latestSenderType || '',
			lastMessageAdminName: conversation.lastMessageAdminName || (latestMessage && latestSenderType === 'staff' ? normalizeStaffDisplayName(latestMessage.adminName) : ''),
			unreadCount: Number(conversation.unreadCount || 0),
			updatedAt: formatTimestamp(conversation.updatedAtRaw || conversation.updatedAt) || conversation.updatedAt || '',
			updatedAtRaw: conversation.lastMessageAt || conversation.updatedAtRaw || conversation.updatedAt || ''
		};
	}

	function normalizeApiMessage(message, conversationId) {
		message = message || {};
		var seenAtRaw = message.seenAtRaw || message.seen_at_raw || message.seen_at || '';
		var deliveredAtRaw = message.deliveredAtRaw || message.delivered_at_raw || message.delivered_at || '';
		var rawConversationId = message.conversationId || message.conversation_id || '';
		var phoneConversationId = normalizePhoneLike(message.client_phone || message.customerPhone || '');
		var messageConversationId = String(rawConversationId || conversationId || phoneConversationId || '').trim();
		var senderType = message.senderType || roleToSenderType(message.role);
		var rawCreatedAt = message.createdAtRaw || message.timestamp || message.createdAt || message.date_entered || '';
		var text = toPlainText(message.content || message.message || message.description || message.text || '');
		return {
			id: message.id || message._id || ('api_' + Date.now() + '_' + Math.random().toString(36).slice(2)),
			conversationId: messageConversationId || conversationId,
			senderType: senderType || 'customer',
			sessionId: message.sessionId || '',
			adminId: message.adminId || '',
			adminName: senderType === 'staff' ? (message.adminName || message.senderName || '') : '',
			adminAvatarUrl: message.adminAvatarUrl || '',
			content: text,
			imageUrl: message.imageUrl || '',
			imageData: message.imageData || '',
			imageName: message.imageName || '',
			attachmentUrl: message.attachmentUrl || '',
			attachmentName: message.attachmentName || '',
			messageType: message.messageType || 'text',
			createdAt: formatTimestamp(rawCreatedAt) || message.createdAt || '',
			createdAtRaw: rawCreatedAt,
			seenAt: formatTimestamp(seenAtRaw) || message.seenAt || '',
			seenAtRaw: seenAtRaw,
			deliveredAt: formatTimestamp(deliveredAtRaw) || message.deliveredAt || '',
			deliveredAtRaw: deliveredAtRaw,
			delivered: !!(message.delivered || deliveredAtRaw)
		};
	}

	function getAdminMessageCacheKey(conversationId) {
		return 'ec_cw_admin_msgs_v3_' + String(conversationId || '');
	}

	function loadCachedConversationMessages(conversationId) {
		var storage = getSessionStorage();
		if (!conversationId || isCustomerMode() || !storage) return false;
		try {
			var legacyKey = 'ec_cw_admin_msgs_' + String(conversationId || '');
			var legacyV2Key = 'ec_cw_admin_msgs_v2_' + String(conversationId || '');
			var cacheKey = getAdminMessageCacheKey(conversationId);
			var raw = storage.getItem(cacheKey) || storage.getItem(legacyV2Key) || storage.getItem(legacyKey);
			if (!raw) return false;
			var cached = JSON.parse(raw);
			if (!Array.isArray(cached) || !cached.length) return false;
			cached.forEach(function (message) {
				upsertMessage(normalizeApiMessage(Object.assign({}, message, { conversationId: conversationId }), conversationId));
			});
			syncConversationPreviewFromLatestMessage(conversationId);
			storage.setItem(cacheKey, JSON.stringify(getMessages(conversationId).slice(-Math.max(config.initialMessageLimit || 15, 30))));
			storage.removeItem(legacyKey);
			storage.removeItem(legacyV2Key);
			return true;
		} catch (e) {
			return false;
		}
	}

	function saveCachedConversationMessages(conversationId) {
		var storage = getSessionStorage();
		if (!conversationId || isCustomerMode() || !storage) return;
		try {
			var limit = config.initialMessageLimit || 15;
			var cached = getMessages(conversationId).slice(-Math.max(limit, 30));
			storage.setItem(getAdminMessageCacheKey(conversationId), JSON.stringify(cached));
		} catch (e) {}
	}

	function mergeApiMessagesDetailed(conversationId, messages, options) {
		var opts = options || {};
		var incoming = (messages || []).map(function (message) {
			return normalizeApiMessage(message, conversationId);
		});
		if (!incoming.length) return {
			changed: false,
			inserted: [],
			stored: []
		};

		var changed = false;
		var inserted = [];
		var stored = [];
		incoming.forEach(function (message) {
			var duplicate = findDuplicateMessage(message);
			var before = getMessageRenderToken(duplicate);
			var storedMessage = upsertMessage(message);
			applyPendingReceipts(message.conversationId || conversationId, { render: false });
			var after = getMessageRenderToken(findDuplicateMessage(message));
			if (before !== after) changed = true;
			if (!duplicate) inserted.push(storedMessage);
			stored.push(storedMessage);
		});

		data.messages.sort(compareMessagesByConversationAndTime);
		syncConversationPreviewFromLatestMessage(conversationId);
		if (opts.cache !== false) saveCachedConversationMessages(conversationId);
		return {
			changed: changed,
			inserted: inserted,
			stored: stored
		};
	}

	function mergeApiMessages(conversationId, messages, options) {
		return mergeApiMessagesDetailed(conversationId, messages, options).changed;
	}

	function loadInitialConversations(options) {
		var opts = options || {};
		if (!isApiEnabled()) return Promise.resolve();
		if (state.hasLoadedInitialConversations && !opts.force) return Promise.resolve();
		// Return the in-flight promise so callers can chain on it instead of getting
		// an empty resolved promise when a load is already running.
		if (state.isLoadingConversations && state._loadConversationsPromise) {
			return state._loadConversationsPromise;
		}
		state.isLoadingConversations = true;
		state._loadConversationsPromise = callLiveChatApi('getConversations', {
			limit: config.initialConversationLimit || 15,
			offset: 0
		}).then(function (apiData) {
			(apiData.conversations || []).forEach(function (conversation) {
				var normalized = normalizeApiConversation(conversation);
				if (!normalized) return;
				ensureConversation(normalized.id, normalized);
				if (conversation.latestMessage) {
					mergeApiMessages(normalized.id, [conversation.latestMessage], { cache: false });
				}
			});
			data.conversations = sortConversationsByActivity(data.conversations);
			renderList();
			renderBadge();
			state.hasLoadedInitialConversations = true;
		}).catch(function (error) {
			debugLog('load conversations failed', error && error.message ? error.message : error);
		}).then(function () {
			state.isLoadingConversations = false;
			state._loadConversationsPromise = null;
		});
		return state._loadConversationsPromise;
	}

	function loadConversationMessages(conversationId) {
		if (!isApiEnabled() || !conversationId || state.loadingMessagesByConversation[conversationId] || state.loadedMessagesByConversation[conversationId]) return Promise.resolve();
		state.loadingMessagesByConversation[conversationId] = true;
		return callLiveChatApi('getMessages', {
			conversation_id: conversationId,
			limit: config.initialMessageLimit || 15,
			offset: 0
		}).then(function (apiData) {
			var changed = mergeApiMessages(conversationId, apiData.messages || []);
			if (apiData.nextCursor) {
				state.messageNextCursorByConversation[conversationId] = apiData.nextCursor;
				delete state.exhaustedOlderMessagesByConversation[conversationId];
			} else {
				delete state.messageNextCursorByConversation[conversationId];
				state.exhaustedOlderMessagesByConversation[conversationId] = true;
			}
			state.loadedMessagesByConversation[conversationId] = true;
			if (changed && isSameConversationId(state.selectedConversationId, conversationId)) {
				renderThread({ focus: true });
			} else if (changed) {
				renderList();
			}
		}).catch(function (error) {
			debugLog('load messages failed', error && error.message ? error.message : error);
		}).then(function () {
			delete state.loadingMessagesByConversation[conversationId];
		});
	}

	function loadOlderConversationMessages(conversationId) {
		if (!isApiEnabled() || !conversationId || isCustomerMode()) return Promise.resolve(false);
		if (state.loadingOlderMessagesByConversation[conversationId] || state.exhaustedOlderMessagesByConversation[conversationId]) return Promise.resolve(false);
		var before = state.messageNextCursorByConversation[conversationId];
		if (!before) return Promise.resolve(false);

		var messagesEl = qs('.ec-cw__messages');
		var previousHeight = messagesEl ? messagesEl.scrollHeight : 0;
		var previousTop = messagesEl ? messagesEl.scrollTop : 0;
		var firstExistingMessage = getMessages(conversationId).filter(function (message) {
			return !isPresenceSystemMessage(message);
		})[0] || null;

		state.loadingOlderMessagesByConversation[conversationId] = true;
		return callLiveChatApi('getMessages', {
			conversation_id: conversationId,
			limit: config.initialMessageLimit || 15,
			before: before
		}).then(function (apiData) {
			var rows = apiData.messages || [];
			var result = mergeApiMessagesDetailed(conversationId, rows);
			var changed = result.changed;
			if (apiData.nextCursor) {
				state.messageNextCursorByConversation[conversationId] = apiData.nextCursor;
			} else {
				delete state.messageNextCursorByConversation[conversationId];
				state.exhaustedOlderMessagesByConversation[conversationId] = true;
			}
			if (changed && isSameConversationId(state.selectedConversationId, conversationId)) {
				var prepended = prependOlderMessagesToThread(conversationId, result.inserted, {
					firstExistingMessage: firstExistingMessage,
					previousHeight: previousHeight,
					previousTop: previousTop
				});
				if (!prepended) {
					renderThread({
						force: true,
						preserveScroll: {
							height: previousHeight,
							top: previousTop
						}
					});
				}
			}
			return changed;
		}).catch(function (error) {
			debugLog('load older messages failed', error && error.message ? error.message : error);
			return false;
		}).then(function (result) {
			delete state.loadingOlderMessagesByConversation[conversationId];
			return result;
		});
	}

	function applyConversationReadState(conversationId, seenAtRaw, seenAt) {
		var conversation = getConversation(conversationId);
		var phone = normalizePhoneLike((conversation && conversation.phone) || conversationId);
		var displaySeenAt = formatTimestamp(seenAtRaw) || seenAt || formatCurrentTime();
		data.messages.forEach(function (message) {
			var sameConversation = isSameConversationId(message.conversationId, conversationId) || (phone && normalizePhoneLike(message.conversationId) === phone);
			if (sameConversation && message.senderType === 'customer' && !message.seenAt) {
				message.seenAt = displaySeenAt;
				message.seenAtRaw = seenAtRaw || message.seenAtRaw || '';
			}
		});
		if (conversation) conversation.unreadCount = 0;
	}

	function markConversationRead(conversationId) {
		if (!isApiEnabled() || !conversationId) return Promise.resolve();
		var conversation = getConversation(conversationId);
		var phone = normalizePhoneLike((conversation && conversation.phone) || conversationId);
		if (!phone) return Promise.resolve();
		return callLiveChatApi('markConversationRead', {
			conversation_id: phone
		}).then(function (apiData) {
			applyConversationReadState(conversationId, apiData && apiData.seenAtRaw, apiData && apiData.seenAt);
			renderList();
		}).catch(function (error) {
			debugLog('mark conversation read failed', error && error.message ? error.message : error);
		});
	}

	function ensureConversation(conversationId, attrs) {
		attrs = attrs || {};
		var phone = normalizePhoneLike(attrs.phone || attrs.customerPhone || attrs.client_phone || '');
		var canonicalId = getCanonicalConversationId(conversationId) || phone;
		// Phone lookup is always enabled: when a new unique convId arrives for the same phone,
		// the existing entry's id is updated in-place (merge) rather than creating a duplicate card.
		var byPhone = phone ? getConversationByPhone(phone) : null;
		var conversation = getConversation(canonicalId) || getConversation(conversationId) || byPhone;
		if (conversation) {
			var oldId = conversation.id;
			if (canonicalId && oldId !== canonicalId) {
				conversation.id = canonicalId;
				moveConversationReferences(oldId, canonicalId);
			}
			if (phone) attrs.phone = phone;
			mergeConversationAttrs(conversation, attrs);
			var duplicate = conversationId !== conversation.id ? getConversation(conversationId) : null;
			mergeConversationInto(conversation, duplicate);
			return conversation;
		}

		var displayName = phone ? ('+' + phone) : 'Khách hàng';
		conversation = Object.assign({
			id: canonicalId,
			customerName: displayName,
			phone: phone,
			lastMessage: '',
			unreadCount: 0,
			updatedAt: ''
		}, attrs);
		data.conversations.unshift(conversation);
		return conversation;
	}

	function mergeConversationAttrs(conversation, attrs) {
		var nextAttrs = Object.assign({}, attrs || {});
		if (!shouldUseConversationPreview(conversation, nextAttrs)) {
			delete nextAttrs.lastMessage;
			delete nextAttrs.lastMessageSenderType;
			delete nextAttrs.lastMessageAdminName;
			delete nextAttrs.updatedAt;
			delete nextAttrs.updatedAtRaw;
		}
		Object.assign(conversation, nextAttrs);
	}

	function moveConversationToTop(conversationId) {
		var index = data.conversations.findIndex(function (conversation) {
			return conversation.id === conversationId;
		});
		if (index <= 0) return;
		var item = data.conversations.splice(index, 1)[0];
		data.conversations.unshift(item);
	}

	function getLatestMessage(conversation) {
		var messages = getMessages(conversation.id).filter(function (message) {
			return message.senderType !== 'system';
		});
		var latest = messages[messages.length - 1];
		return latest ? latest.content : conversation.lastMessage;
	}

	function getLatestNonSystemMessage(conversationId) {
		var messages = getMessages(conversationId).filter(function (message) {
			return message.senderType !== 'system';
		});
		return messages[messages.length - 1] || null;
	}

	function buildConversationPreviewData(senderType, senderName, content) {
		var text = String(content || '').trim();
		var normalizedSenderType = senderType || '';
		var normalizedSenderName = String(senderName || '').trim();
		if (!text) {
			return {
				text: '',
				senderType: normalizedSenderType,
				senderName: normalizedSenderName,
				content: ''
			};
		}
		if (normalizedSenderType === 'staff') {
			normalizedSenderName = normalizeStaffDisplayName(normalizedSenderName);
			return {
				text: normalizedSenderName + ': ' + text,
				senderType: normalizedSenderType,
				senderName: normalizedSenderName,
				content: text
			};
		}
		if (normalizedSenderType === 'ai') {
			return {
				text: 'AI: ' + text,
				senderType: normalizedSenderType,
				senderName: 'AI',
				content: text
			};
		}
		return {
			text: text,
			senderType: normalizedSenderType,
			senderName: normalizedSenderName,
			content: text
		};
	}

	function getConversationPreviewData(conversation) {
		conversation = conversation || {};
		var latestMessage = getLatestNonSystemMessage(conversation.id);
		var realtimePreview = String(conversation.lastMessage || '').trim();
		if (realtimePreview) {
			var realtimeDate = parseDateTime(conversation.updatedAtRaw || conversation.updatedAt);
			var latestDate = latestMessage && parseDateTime(latestMessage.createdAtRaw || latestMessage.createdAt);
			if (!latestMessage || (realtimeDate && (!latestDate || realtimeDate.getTime() > latestDate.getTime()))) {
				var previewSenderType = conversation.lastMessageSenderType || (latestMessage && latestMessage.senderType) || '';
				var previewSenderName = conversation.lastMessageAdminName || (latestMessage ? getStaffDisplayName(latestMessage) : '');
				return buildConversationPreviewData(previewSenderType, previewSenderName, realtimePreview);
			}
		}
		var content = latestMessage ? latestMessage.content : realtimePreview;
		content = String(content || '').trim();
		if (!content) return buildConversationPreviewData('', '', '');
		if (!latestMessage) return buildConversationPreviewData('', '', content);
		return buildConversationPreviewData(
			latestMessage.senderType,
			latestMessage.senderType === 'staff' ? getStaffDisplayName(latestMessage) : '',
			content
		);
	}

	function getConversationPreviewText(conversation) {
		return getConversationPreviewData(conversation).text;
	}

	function updateLastMessage(conversationId, content, resetUnread, updatedAt) {
		var conversation = getConversation(conversationId);
		if (!conversation) return;
		conversation.lastMessage = toPlainText(content);
		conversation.updatedAt = updatedAt || formatCurrentTime();
		if (resetUnread !== false) conversation.unreadCount = 0;
	}

	function updateConversationLastMessageMeta(conversationId, message) {
		var conversation = getConversation(conversationId);
		if (!conversation || !message) return;
		conversation.lastMessageSenderType = message.senderType || '';
		conversation.lastMessageAdminName = message.senderType === 'staff'
			? getStaffDisplayName(message)
			: '';
		conversation.updatedAtRaw = message.createdAtRaw || message.timestamp || conversation.updatedAtRaw || '';
	}

	function syncConversationPreviewFromLatestMessage(conversationId) {
		var latest = getLatestNonSystemMessage(conversationId);
		if (!latest) return;
		var conversation = getConversation(conversationId);
		var latestTime = getMessageTimeMs(latest);
		var conversationTime = getConversationTimeMs(conversation);
		if (conversationTime !== null && latestTime !== null && latestTime < conversationTime) return;
		updateLastMessage(conversationId, latest.content || (latest.imageUrl ? 'Đã gửi ảnh đính kèm.' : ''), false, latest.createdAt || formatCurrentTime());
		updateConversationLastMessageMeta(conversationId, latest);
	}

	function getUnreadTotal() {
		return data.conversations.reduce(function (total, conversation) {
			return total + Number(conversation.unreadCount || 0);
		}, 0);
	}

	function getInitials(name) {
		var parts = (name || '').trim().split(/\s+/);
		if (parts.length >= 2) {
			return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
		}
		return (name || '?').substring(0, 2).toUpperCase();
	}

	/* ---- Avatar colors: stable by phone/admin name ---- */
	var avatarColors = [
		{ bg: '#dbeafe', text: '#1d4ed8' },
		{ bg: '#dcfce7', text: '#15803d' },
		{ bg: '#fef3c7', text: '#b45309' },
		{ bg: '#fce7f3', text: '#be185d' },
		{ bg: '#ede9fe', text: '#7c3aed' }
	];
	var avatarColorMap = {};
	function getStableColorIndex(key) {
		var text = String(key || 'unknown').trim().toLowerCase();
		var hash = 0;
		for (var i = 0; i < text.length; i++) {
			hash = ((hash << 5) - hash) + text.charCodeAt(i);
			hash |= 0;
		}
		return Math.abs(hash) % avatarColors.length;
	}
	function getAvatarColor(key) {
		var normalized = String(key || 'unknown').trim().toLowerCase();
		if (!avatarColorMap[normalized]) {
			avatarColorMap[normalized] = avatarColors[getStableColorIndex(normalized)];
		}
		return avatarColorMap[normalized];
	}

	function injectStyles() {
		if (document.getElementById(STYLE_ID)) return;
		var style = document.createElement('style');
		style.id = STYLE_ID;
		var desktopStyles = [
			/* reset */
			'.ec-cw,.ec-cw *{box-sizing:border-box;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Arial,sans-serif;letter-spacing:0;margin:0;padding:0}',

			/* root */
			'.ec-cw{--chat-primary:' + config.primaryColor + ';--chat-bg:#f8fbff;--chat-surface:var(--white-color,#fff);--chat-border:var(--border-color,#dee2e6);--chat-text:var(--text-primary-color,#012970);--chat-muted:#667085;--chat-agent:#eef4ff;--chat-soft:#eaf2ff;--chat-soft-hover:#dceaff;--chat-success:var(--success-color,#82ce34);--chat-danger:var(--danger-color,#ec2029);position:fixed;bottom:24px;right:24px;z-index:99999;color:var(--chat-text)}',
			'@keyframes ecCwSlideInRight{from{opacity:0;transform:translateX(20px) scale(.95)}to{opacity:1;transform:translateX(0) scale(1)}}',
			'@keyframes ecCwSlideInLeft{from{opacity:0;transform:translateX(-20px) scale(.95)}to{opacity:1;transform:translateX(0) scale(1)}}',
			'@keyframes ecCwSlideUpFade{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}',
			'@keyframes ecCwReconnectSpin{to{transform:rotate(360deg)}}',

			/* toggle button */
			'.ec-cw__toggle{align-items:center!important;background:var(--chat-primary)!important;border:1px solid rgba(255,255,255,.3)!important;border-radius:50%!important;box-shadow:0 10px 28px rgba(10,88,202,.28),0 2px 8px rgba(0,0,0,.08)!important;color:#fff!important;cursor:pointer;display:flex!important;font-size:0!important;font-weight:600;height:58px!important;justify-content:center!important;min-height:58px!important;min-width:58px!important;overflow:visible!important;padding:0!important;position:relative;transition:transform .2s ease,opacity .2s,box-shadow .2s;width:58px!important}',
			'.ec-cw__toggle:hover{transform:scale(1.05)}',
			'.ec-cw__toggle span:not(.ec-cw__toggle-icon){display:none!important}',
			'.ec-cw__toggle-icon{font-size:0;line-height:1}',
			'.ec-cw__toggle-icon:before{content:"";display:block;width:30px;height:30px;background:currentColor;mask:url("data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 24 24%27 fill=%27none%27 stroke=%27black%27 stroke-width=%272%27 stroke-linecap=%27round%27 stroke-linejoin=%27round%27%3E%3Cpath d=%27M7.9 20A9 9 0 1 0 4 16.1L2 22Z%27/%3E%3C/svg%3E") center/contain no-repeat;-webkit-mask:url("data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 24 24%27 fill=%27none%27 stroke=%27black%27 stroke-width=%272%27 stroke-linecap=%27round%27 stroke-linejoin=%27round%27%3E%3Cpath d=%27M7.9 20A9 9 0 1 0 4 16.1L2 22Z%27/%3E%3C/svg%3E") center/contain no-repeat}',
			'.ec-cw__badge{align-items:center;background:var(--chat-danger);border:2px solid #fff;border-radius:50%;color:#fff;display:flex;font-size:10px;font-weight:700;height:20px;justify-content:center;line-height:1;min-width:20px;position:absolute;right:-4px;text-align:center;top:-4px}',

			/* panel */
			'.ec-cw__panel{background:var(--chat-surface);border:1px solid var(--chat-border);border-radius:28px;bottom:0;box-shadow:0 12px 48px rgba(0,0,0,.15);display:grid;grid-template-rows:64px 1fr;height:520px;opacity:0;overflow:hidden;pointer-events:none;position:absolute;right:0;transform:scale(.85) translateY(20px);transform-origin:bottom right;transition:transform .38s cubic-bezier(.34,1.56,.64,1),opacity .28s ease;width:360px}',
			'.ec-cw.is-open .ec-cw__panel{opacity:1;pointer-events:auto;transform:scale(1) translateY(0)}',
			'.ec-cw.is-open .ec-cw__toggle{opacity:0;pointer-events:none;transform:scale(.8)}',
			'.ec-cw.has-thread .ec-cw__panel{grid-template-columns:330px minmax(0,1fr);grid-template-rows:64px 1fr;width:760px}',
			'.ec-cw--customer.has-thread .ec-cw__panel{grid-template-columns:1fr;grid-template-rows:64px 1fr;width:360px}',

			/* header */
			'.ec-cw__header{align-items:center;background:var(--chat-primary);border-radius:28px 28px 0 0;color:#fff;display:flex;gap:12px;padding:0 24px}',
			'.ec-cw--admin.has-thread .ec-cw__header{border-radius:0 28px 0 0;grid-column:2;grid-row:1;padding-left:20px;padding-right:18px}',
			'.ec-cw__header-main{flex:1;min-width:0;position:relative}',
			'.ec-cw__header-customer-avatar{align-items:center;display:flex;flex:0 0 auto;margin-right:2px}',
			'.ec-cw__header-customer-avatar:empty{display:none}',
			'.ec-cw__header-avatar-chip{align-items:center;border:2px solid rgba(255,255,255,.9);border-radius:50%;box-shadow:0 1px 3px rgba(0,0,0,.15);display:flex;font-size:10px;font-weight:800;height:34px;justify-content:center;letter-spacing:0;margin-left:-8px;overflow:hidden;text-transform:uppercase;width:34px}',
			'.ec-cw__header-avatar-chip:first-child{margin-left:0}',
			'.ec-cw__header-avatar-chip img{display:block;height:100%;object-fit:cover;width:100%}',
			'.ec-cw__header-avatar-chip--customer{height:38px;width:38px}',
			'.ec-cw__header-avatar-chip--admin{height:24px;width:24px;font-size:8px;margin-left:-6px}',
			'.ec-cw__header-admin-avatars .ec-cw__header-avatar-chip:first-child{margin-left:0}',
			'.ec-cw__header-title{flex:1;font-size:16px;font-weight:600;letter-spacing:0;line-height:1.2;text-transform:none}',
			'.ec-cw__header-subtitle{align-items:center;display:flex;font-size:12px;font-weight:400;gap:5px;opacity:.9}',
			'.ec-cw__header-subtitle:before{background:var(--chat-success);border:1px solid rgba(255,255,255,.5);border-radius:50%;content:"";display:block;height:8px;width:8px}',
			'.ec-cw__header-subtitle.is-offline:before{background:#94a3b8}',
			'.ec-cw__header-subtitle.is-online:before{background:var(--chat-success)}',
			'.ec-cw__header-status-text{white-space:nowrap}',
			'.ec-cw__header-admin-avatars{align-items:center;cursor:pointer;display:inline-flex;margin-left:8px;min-height:24px}',
			'.ec-cw__header-admin-avatars:empty{display:none}',
			'.ec-cw__header-back{align-items:center;background:transparent;border:0;border-radius:50%;color:#fff;cursor:pointer;display:none;flex:0 0 auto;height:44px;justify-content:center;width:44px}',
			'.ec-cw__header-back:hover{background:rgba(255,255,255,.12)}',
			'.ec-cw__admin-participants-popover{background:#fff;border:1px solid #dbe3df;border-radius:10px;box-shadow:0 14px 34px rgba(15,23,42,.18);color:#0f172a;display:none;left:0;min-width:210px;padding:10px;position:absolute;top:48px;z-index:2}',
			'.ec-cw__admin-participants-popover.is-open{display:block}',
			'.ec-cw__participant-title{color:#64748b;font-size:11px;font-weight:700;letter-spacing:.3px;margin-bottom:8px;text-transform:uppercase}',
			'.ec-cw__participant-row{align-items:center;display:flex;gap:9px;padding:7px 2px}',
			'.ec-cw__participant-row+.ec-cw__participant-row{border-top:1px solid #eef2f7}',
			'.ec-cw__participant-avatar{align-items:center;border-radius:50%;display:flex;flex:0 0 30px;font-size:9px;font-weight:800;height:30px;justify-content:center;letter-spacing:0;overflow:hidden;text-transform:uppercase;width:30px}',
			'.ec-cw__participant-avatar img{display:block;height:100%;object-fit:cover;width:100%}',
			'.ec-cw__participant-info{min-width:0}',
			'.ec-cw__participant-name{color:#0f172a;font-size:13px;font-weight:700;line-height:1.25;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}',
			'.ec-cw__participant-role{color:#64748b;font-size:11px;line-height:1.25;margin-top:2px}',
			'.ec-cw__header-actions{align-items:center;display:flex;gap:2px}',
			'.ec-cw__header-actions button{align-items:center;background:transparent;border:none;border-radius:50%;color:#fff;cursor:pointer;display:flex;font-size:16px;height:40px;justify-content:center;width:40px}',
			'.ec-cw__header-actions button:hover{background:rgba(255,255,255,.1);color:#fff}',
			'.ec-cw__minimize{display:none!important}',
			'.ec-cw__list-close{display:none}',

			/* body */
			'.ec-cw__body{display:block;min-height:0;overflow:hidden}',
			'.ec-cw.has-thread .ec-cw__body{display:contents;min-width:0}',
			'.ec-cw--customer.has-thread .ec-cw__body{display:block}',

			/* conversation list */
			'.ec-cw__list{align-items:stretch;background:#f8fafc;display:flex;flex-direction:column;height:100%;overflow:hidden}',
			'.ec-cw__list-chrome{flex:0 0 auto}',
			'.ec-cw__list-scroll{flex:1;min-height:0;overflow-x:hidden;overflow-y:auto;padding:8px 0}',
			'.ec-cw__list-scroll::-webkit-scrollbar{width:4px}',
			'.ec-cw__list-scroll::-webkit-scrollbar-thumb{background:#cbd5e1;border-radius:4px}',
			'.ec-cw.has-thread .ec-cw__list{border-right:1px solid #e2e8f0;grid-column:1;grid-row:1/3;order:1}',
			'.ec-cw--admin.has-thread .ec-cw__list{background:#fff;padding:0}',
			'.ec-cw--customer .ec-cw__list{display:none}',

			/* list header */
'.ec-cw__list-header{color:#64748b;flex:0 0 auto;font-size:11px;font-weight:600;letter-spacing:.5px;padding:4px 14px 8px;position:sticky;top:0;text-transform:uppercase;z-index:3}',
			'.ec-cw__list-title{min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}',
			'.ec-cw--admin:not(.has-thread) .ec-cw__list-header--empty{display:none}',
			'.ec-cw__list-header--empty .ec-cw__header-customer-avatar{display:none}',
			'.ec-cw--admin.has-thread .ec-cw__list-header{align-items:center;background:var(--chat-primary);color:#fff;display:flex;font-size:16px;font-weight:700;gap:12px;height:64px;letter-spacing:0;padding:0 30px;text-transform:none}',
			'.ec-cw__list-header .ec-cw__header-customer-avatar{margin-right:0}',
'.ec-cw__list-search{background:#fff;flex:0 0 auto;padding:10px 14px;position:sticky;top:0;z-index:2}',
			'.ec-cw--admin.has-thread .ec-cw__list-search{background:#fff;padding:10px 14px}',
			'.ec-cw--admin.has-thread .ec-cw__list-search{top:64px}',
			'.ec-cw__list-search:before{bottom:0;content:"";height:16px;left:28px;margin:auto 0;position:absolute;top:0;width:16px;background:#64748b;mask:url("data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 24 24%27 fill=%27none%27 stroke=%27black%27 stroke-width=%272%27 stroke-linecap=%27round%27 stroke-linejoin=%27round%27%3E%3Ccircle cx=%2711%27 cy=%2711%27 r=%278%27/%3E%3Cpath d=%27m21 21-4.3-4.3%27/%3E%3C/svg%3E") center/contain no-repeat;-webkit-mask:url("data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 24 24%27 fill=%27none%27 stroke=%27black%27 stroke-width=%272%27 stroke-linecap=%27round%27 stroke-linejoin=%27round%27%3E%3Ccircle cx=%2711%27 cy=%2711%27 r=%278%27/%3E%3Cpath d=%27m21 21-4.3-4.3%27/%3E%3C/svg%3E") center/contain no-repeat}',
			'.ec-cw--admin.has-thread .ec-cw__list-search:before{bottom:0;left:28px;margin:auto 0;top:0}',
			'.ec-cw__list-search input{background:#f3f4f4;border:1px solid #d9dedb;border-radius:18px;color:#0f172a;font-size:12px;height:34px;outline:none;padding:0 10px 0 34px;width:100%}',
			'.ec-cw--admin.has-thread .ec-cw__list-search input{background:#f3f4f4;border-color:#d9dedb;border-radius:18px;height:34px}',
			'.ec-cw__list-search input:focus{background:#fff;border-color:var(--chat-primary);box-shadow:0 0 0 2px rgba(10,88,202,.1)}',
			'.ec-cw__list-search input::placeholder{color:#64748b}',
			'.ec-cw__list-empty{align-items:center;color:#64748b;display:flex;flex:1;flex-direction:column;justify-content:center;min-height:0;padding:32px 28px;text-align:center}',
			'.ec-cw__list-empty-icon{align-items:center;background:var(--chat-soft);border:1px solid #cfe0ff;border-radius:50%;color:var(--chat-primary);display:flex;height:58px;justify-content:center;margin-bottom:14px;width:58px}',
			'.ec-cw__list-empty-icon:before{content:"";display:block;width:28px;height:28px;background:currentColor;mask:url("data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 24 24%27 fill=%27none%27 stroke=%27black%27 stroke-width=%272%27 stroke-linecap=%27round%27 stroke-linejoin=%27round%27%3E%3Cpath d=%27M21 15a4 4 0 0 1-4 4H7l-4 4V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z%27/%3E%3Cpath d=%27M8 9h8M8 13h5%27/%3E%3C/svg%3E") center/contain no-repeat;-webkit-mask:url("data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 24 24%27 fill=%27none%27 stroke=%27black%27 stroke-width=%272%27 stroke-linecap=%27round%27 stroke-linejoin=%27round%27%3E%3Cpath d=%27M21 15a4 4 0 0 1-4 4H7l-4 4V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z%27/%3E%3Cpath d=%27M8 9h8M8 13h5%27/%3E%3C/svg%3E") center/contain no-repeat}',
			'.ec-cw__list-empty-title{color:#0f172a;font-size:15px;font-weight:700;line-height:1.3;margin-bottom:6px}',
			'.ec-cw__list-empty-text{color:#64748b;font-size:13px;line-height:1.45;max-width:240px}',

			/* conversation item */
			'.ec-cw__conv{align-items:center;appearance:none;background:transparent;border:none;border-radius:8px;cursor:pointer;display:flex!important;flex:0 0 78px;gap:12px;height:78px;margin:0 0 1px;max-height:78px;max-width:100%!important;min-height:78px;min-width:100%!important;overflow:hidden;padding:12px 14px;position:relative;text-align:left;transition:background .2s ease,box-shadow .2s ease;width:100%!important}',
			'.ec-cw__conv:hover{background:#eef0f2;box-shadow:0 8px 20px rgba(15,23,42,.08)}',
			'.ec-cw__conv--empty{background:#f8fafc}',
			'.ec-cw__conv--empty:hover{background:#f1f3f5}',
			'.ec-cw__conv.is-active{background:var(--chat-soft)}',
			'.ec-cw__conv.is-active:hover{background:var(--chat-soft-hover);box-shadow:0 8px 20px rgba(15,23,42,.08)}',
			'.ec-cw__conv.is-active:before{background:var(--chat-primary);border-radius:0 8px 8px 0;bottom:0;content:"";position:absolute;right:0;top:0;width:4px}',
			'.ec-cw__conv-avatar{align-items:center;border-radius:50%;display:flex;flex:0 0 48px;font-size:14px;font-weight:700;height:48px;justify-content:center;letter-spacing:0}',
			'.ec-cw__conv-body{display:grid;flex:1;grid-template-rows:22px 22px;min-width:0;overflow:hidden;row-gap:4px}',
			'.ec-cw__conv-top{align-items:center;display:flex;gap:8px;height:22px;min-height:22px}',
			'.ec-cw__conv-name{color:#0f172a;flex:1;font-size:15px;font-weight:700;line-height:22px;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}',
			'.ec-cw__conv-time{color:#64748b;flex:0 0 auto;font-size:11px;line-height:22px}',
			'.ec-cw__conv-preview-row{align-items:center;display:flex;gap:8px;height:22px;min-width:0;overflow:visible}',
			'.ec-cw__conv-preview{color:#64748b;flex:1;font-size:14px;height:22px;line-height:22px;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}',
			'.ec-cw__conv-preview-admin{color:#1d4ed8;font-weight:700}',
			'.ec-cw__conv-preview-message{color:inherit}',
			'.ec-cw__conv-preview.is-empty{color:#94a3b8;font-style:italic}',
			'.ec-cw__conv-preview.is-typing{align-items:center;color:var(--chat-primary);display:inline-flex;font-style:italic;font-weight:600;gap:6px}',
			'.ec-cw__conv-typing-dots{display:inline-flex;gap:3px;line-height:1}',
			'.ec-cw__conv-typing-dots span{animation:ecCwTypingDot 1.2s infinite ease-in-out;background:currentColor;border-radius:50%;display:block;height:4px;width:4px}',
			'.ec-cw__conv-typing-dots span:nth-child(2){animation-delay:.16s}',
			'.ec-cw__conv-typing-dots span:nth-child(3){animation-delay:.32s}',
			'.ec-cw__unread{align-items:center;background:#ef4444;border-radius:50%;color:#fff;display:flex;flex:0 0 20px;font-size:11px;font-weight:700;height:20px;justify-content:center;line-height:20px;margin-left:auto;min-width:20px;text-align:center;width:20px}',

			/* Delivery tick in conversation card */
			'.ec-cw__conv-tick{align-items:center;display:flex;flex:0 0 auto;font-size:13px;line-height:1;margin-left:auto;opacity:1}',
			'.ec-cw__conv-tick--seen{color:var(--chat-success)}',
			'.ec-cw__conv-tick--sent{color:#94a3b8}',

			/* thread pane */
			'.ec-cw__thread{display:none;flex-direction:column;height:100%;min-height:0;min-width:0;overflow:hidden}',
			'.ec-cw.has-thread .ec-cw__thread{display:flex;grid-column:2;grid-row:2;order:2}',
			'.ec-cw--customer .ec-cw__thread{display:flex}',
			'.ec-cw--customer .ec-cw__back{display:none!important}',
			'.ec-cw__reconnect-status{align-items:center;background:#eff6ff;border-bottom:1px solid #bfdbfe;color:#1d4ed8;display:none;flex:0 0 auto;font-family:Inter,-apple-system,BlinkMacSystemFont,"Segoe UI",Arial,sans-serif;font-size:12px;font-weight:700;gap:8px;justify-content:center;line-height:1.25;padding:9px 14px;text-align:center}',
			'.ec-cw__thread.is-reconnecting .ec-cw__reconnect-status,.ec-cw__thread.is-reconnected .ec-cw__reconnect-status{display:flex}',
			'.ec-cw__thread.is-reconnected .ec-cw__reconnect-status{background:#ecfdf3;border-bottom-color:#bbf7d0;color:#15803d;opacity:0;transform:translateY(-8px);transition:opacity .28s ease,transform .28s ease}',
			'.ec-cw__thread.is-reconnected-show .ec-cw__reconnect-status{opacity:1;transform:translateY(0)}',
			'.ec-cw__reconnect-icon{animation:ecCwReconnectSpin .9s linear infinite;color:#2563eb;display:inline-flex;flex:0 0 auto;height:16px;width:16px}',
			'.ec-cw__thread.is-reconnected .ec-cw__reconnect-icon{animation:none;color:#16a34a}',

			/* back button */
			'.ec-cw__back{display:none;align-items:center;background:#fff;border:none;border-bottom:1px solid var(--chat-border);color:var(--chat-primary);cursor:pointer;font-size:12px;font-weight:600;gap:6px;padding:8px 12px;text-align:left;width:100%}',
			'.ec-cw__back:hover{background:var(--chat-soft)}',

			/* customer info bar */
			'.ec-cw__customer{display:none}',
			'.ec-cw__customer-top{align-items:center;display:flex;gap:10px;margin-bottom:6px}',
			'.ec-cw__customer-avatar{align-items:center;border-radius:50%;display:flex;flex:0 0 34px;font-size:12px;font-weight:700;height:34px;justify-content:center}',
			'.ec-cw__customer-name{color:#0f172a;font-size:13px;font-weight:600;line-height:1.3}',
			'.ec-cw__customer-meta{display:flex;flex-wrap:wrap;gap:4px 12px}',
			'.ec-cw__customer-field{align-items:center;color:#64748b;display:flex;font-size:11px;gap:4px}',
			'.ec-cw__customer-field strong{color:#334155;font-weight:600}',

			/* messages */
			'.ec-cw__messages{background:var(--chat-bg);display:flex;flex:1;flex-direction:column;gap:14px;min-height:0;overflow-x:hidden;overflow-y:auto;padding:16px}',
			'.ec-cw__messages.is-settling{visibility:hidden}',
			'.ec-cw__messages::-webkit-scrollbar{width:6px}',
			'.ec-cw__messages::-webkit-scrollbar-thumb{background:#ccc;border-radius:3px}',
			'.ec-cw__date-divider{align-self:center;color:#64748b;font-size:12px;font-weight:700;line-height:1;margin:6px 0 2px;padding:2px 8px;text-align:center}',
			'.ec-cw__msg{align-items:flex-end;display:flex;gap:8px;margin-bottom:0;width:100%}',
			'.ec-cw__msg--customer{flex-direction:row}',
			'.ec-cw__msg--ai,.ec-cw__msg--staff{flex-direction:row-reverse}',
			'.ec-cw__msg--agent{flex-direction:row}',
			'.ec-cw__msg--own{flex-direction:row-reverse}',
			'.ec-cw__msg.is-new.ec-cw__msg--staff,.ec-cw__msg.is-new.ec-cw__msg--own{animation:ecCwSlideInRight .28s cubic-bezier(.25,.8,.25,1) forwards}',
			'.ec-cw__msg.is-new.ec-cw__msg--customer,.ec-cw__msg.is-new.ec-cw__msg--agent,.ec-cw__msg.is-new.ec-cw__msg--ai{animation:ecCwSlideInLeft .28s cubic-bezier(.25,.8,.25,1) forwards}',
			'.ec-cw__msg--system{justify-content:center;margin:8px 0}',
			'.ec-cw__msg.is-new.ec-cw__msg--system{animation:ecCwSlideUpFade .22s ease forwards}',
			'.ec-cw__msg-avatar{align-items:center;border-radius:50%;display:flex;flex:0 0 28px;font-size:10px;font-weight:700;height:28px;justify-content:center;margin-bottom:16px}',
			'.ec-cw__msg-avatar img{border-radius:50%;display:block;height:28px;object-fit:cover;width:28px}',
			'.ec-cw__msg--staff .ec-cw__msg-avatar,.ec-cw__msg--own .ec-cw__msg-avatar{display:none}',
			'.ec-cw__msg-stack{display:flex;flex-direction:column;max-width:85%;min-width:0}',
			'.ec-cw__msg--staff .ec-cw__msg-stack,.ec-cw__msg--own .ec-cw__msg-stack{align-items:flex-end}',
			'.ec-cw__msg--customer .ec-cw__msg-stack,.ec-cw__msg--agent .ec-cw__msg-stack,.ec-cw__msg--ai .ec-cw__msg-stack{align-items:flex-start}',

			'.ec-cw__bubble{border-radius:20px;box-sizing:border-box;max-width:100%;overflow-wrap:break-word;padding:10px 16px;word-break:break-word;word-wrap:break-word;width:fit-content}',
			'.ec-cw__msg--customer .ec-cw__bubble{background:#f4f7fb;border:1px solid var(--chat-border);border-bottom-left-radius:4px}',
			'.ec-cw__msg--ai .ec-cw__bubble{background:#e7f1ff;border:1px solid #cfe2ff;border-bottom-left-radius:4px}',
			'.ec-cw__msg--staff .ec-cw__bubble{background:var(--chat-primary);border-bottom-right-radius:4px;color:#fff}',
			'.ec-cw__msg--agent .ec-cw__bubble{background:var(--chat-agent);border-bottom-left-radius:4px}',
			'.ec-cw__msg--own .ec-cw__bubble{background:var(--chat-primary);border-bottom-right-radius:4px;color:#fff}',
			'.ec-cw__msg--staff .ec-cw__bubble .ec-cw__label{color:rgba(255,255,255,.7)}',
			'.ec-cw__msg--staff .ec-cw__bubble .ec-cw__content{color:#fff}',
			'.ec-cw__msg--own .ec-cw__bubble .ec-cw__label{color:rgba(255,255,255,.7)}',
			'.ec-cw__msg--own .ec-cw__bubble .ec-cw__content{color:#fff}',
			'.ec-cw__label{display:block;font-size:10px;font-weight:700;letter-spacing:.4px;line-height:1;margin-bottom:6px;text-transform:none}',
			'.ec-cw__msg--customer .ec-cw__label{color:#64748b}',
			'.ec-cw__msg--ai .ec-cw__label{color:#1d4ed8}',
			'.ec-cw__msg--staff .ec-cw__label,.ec-cw__msg--own .ec-cw__label{color:rgba(255,255,255,.78)}',
			'.ec-cw__msg--agent .ec-cw__label{color:#64748b}',
			'.ec-cw__content{color:inherit;font-size:14px;line-height:1.4;overflow-wrap:break-word;white-space:pre-wrap;word-break:break-word;word-wrap:break-word}',
			'.ec-cw__content:after{clear:both;content:"";display:block}',
			'.ec-cw__time-s{color:var(--chat-muted);display:block;font-size:11px;margin-top:4px;text-align:left}',
			'.ec-cw__msg--staff .ec-cw__time-s,.ec-cw__msg--own .ec-cw__time-s{color:var(--chat-muted);margin-right:4px;text-align:right}',

			'.ec-cw__receipt{align-items:center;display:inline-flex;font-size:11px;font-weight:500;gap:4px;line-height:1;text-align:right;white-space:nowrap}',
			'.ec-cw__receipt--bubble{float:right;margin:5px 0 0 10px;position:relative}',
			'.ec-cw__receipt--bubble[data-tooltip]:hover:after{background:#0f172a;border-radius:6px;box-shadow:0 6px 18px rgba(15,23,42,.18);color:#fff;content:attr(data-tooltip);font-size:11px;font-weight:600;line-height:1;padding:6px 8px;position:absolute;right:0;top:calc(100% + 7px);white-space:nowrap;z-index:4}',
			'.ec-cw__receipt--bubble[data-tooltip]:hover:before{border:5px solid transparent;border-bottom-color:#0f172a;content:"";position:absolute;right:10px;top:calc(100% - 3px);z-index:4}',
			'.ec-cw__receipt-time{color:rgba(255,255,255,.72)}',
			'.ec-cw__msg--customer .ec-cw__receipt-time,.ec-cw__msg--agent .ec-cw__receipt-time,.ec-cw__msg--ai .ec-cw__receipt-time{color:#64748b}',
			'.ec-cw__receipt-tick{align-items:center;display:flex;flex:0 0 auto}',
			'.ec-cw__receipt[data-retry="1"]{cursor:pointer}',
			'.ec-cw__receipt-tick--failed{color:#fecaca}',
			'.ec-cw__receipt-error-mark{align-items:center;border:1px solid currentColor;border-radius:50%;display:inline-flex;font-size:10px;font-weight:800;height:14px;justify-content:center;line-height:14px;width:14px}',
			'.ec-cw__receipt-sending-mark{animation:ecCwTypingDot 1.2s infinite ease-in-out;color:rgba(255,255,255,.78);font-size:14px;font-weight:800;line-height:10px}',

			'.ec-cw__sys-note{background:transparent;border:none;border-radius:8px;color:var(--chat-muted);font-size:12px;font-style:italic;max-width:90%;padding:4px 12px;text-align:center}',
			'.ec-cw__sys-note .ec-cw__time-s{text-align:center}',
			'.ec-cw__typing{align-items:flex-end;display:flex;gap:8px;margin:2px 0 0;width:100%}',
			'.ec-cw__typing--staff{flex-direction:row-reverse}',
			'.ec-cw__typing--staff .ec-cw__typing-bubble{background:var(--chat-primary);border-color:var(--chat-primary);border-bottom-left-radius:18px;border-bottom-right-radius:4px}',
			'.ec-cw__typing--staff .ec-cw__typing-dot{background:rgba(255,255,255,.82)}',
			'.ec-cw__typing-avatar{align-items:center;border-radius:50%;display:flex;flex:0 0 28px;font-size:10px;font-weight:800;height:28px;justify-content:center;letter-spacing:0;text-transform:uppercase;width:28px}',
			'.ec-cw__typing-avatar img{border-radius:50%;display:block;height:28px;object-fit:cover;width:28px}',
			'.ec-cw__typing-bubble{align-items:center;background:#f4f7fb;border:1px solid var(--chat-border);border-radius:18px;border-bottom-left-radius:4px;display:flex;gap:4px;height:34px;padding:0 14px}',
			'.ec-cw__typing-dot{animation:ecCwTypingDot 1.2s infinite ease-in-out;background:var(--chat-muted);border-radius:50%;display:block;height:5px;width:5px}',
			'.ec-cw__typing-dot:nth-child(2){animation-delay:.16s}',
			'.ec-cw__typing-dot:nth-child(3){animation-delay:.32s}',
			'@keyframes ecCwTypingDot{0%,80%,100%{opacity:.35;transform:translateY(0)}40%{opacity:1;transform:translateY(-3px)}}',

			/* image preview */
			'.ec-cw__img-preview{border-top:1px solid var(--chat-border);display:none;padding:8px 16px}',
			'.ec-cw__img-preview.is-visible{display:block}',
			'.ec-cw__img-chip{align-items:center;background:#f0f6ff;border:1px solid #bfdbfe;border-radius:8px;display:flex;gap:8px;padding:6px 8px}',
			'.ec-cw__img-chip img{border-radius:4px;height:32px;object-fit:cover;width:44px}',
			'.ec-cw__img-chip span{color:#475569;flex:1;font-size:12px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}',
			'.ec-cw__img-chip button{align-items:center;background:#ef4444;border:none;border-radius:50%;color:#fff;cursor:pointer;display:flex;font-size:12px;height:20px;justify-content:center;line-height:1;width:20px}',

			/* reply bar */
			'.ec-cw__reply{align-items:end;background:#fff;border-top:1px solid var(--chat-border);display:grid;flex:0 0 auto;gap:8px;grid-template-columns:40px minmax(0,1fr) 40px;min-height:56px;padding:8px 16px}',
			'.ec-cw--no-upload .ec-cw__reply{grid-template-columns:minmax(0,1fr) 40px}',
			'.ec-cw__upload{align-items:center;background:#f4f7fb;border:1px solid var(--chat-border);border-radius:50%;color:var(--chat-muted);cursor:pointer;display:flex;height:40px;justify-content:center;transition:background .12s;width:40px}',
			'.ec-cw--no-upload .ec-cw__upload{display:none}',
			'.ec-cw__upload:hover{background:var(--chat-soft);color:var(--chat-primary)}',
			'.ec-cw__upload input{display:none}',
			'.ec-cw__reply textarea{background:#f4f7fb;border:1px solid var(--chat-border);border-radius:20px;color:var(--chat-text);display:block;font-size:14px;height:40px;line-height:20px;max-height:104px;min-height:40px;outline:none;overflow-y:hidden;padding:9px 16px;resize:none;transition:border-color .15s,box-shadow .15s;width:100%}',
			'.ec-cw__reply textarea:focus{border-color:var(--chat-primary);box-shadow:0 0 0 2px rgba(10,88,202,.1);background:#fff}',
			'.ec-cw__reply textarea::placeholder{color:var(--chat-muted)}',
			'.ec-cw__send{align-items:center!important;background:var(--chat-primary);border:none!important;border-radius:50%!important;color:#fff;cursor:pointer;display:flex!important;font-size:0!important;font-weight:700;height:40px!important;justify-content:center!important;line-height:40px!important;min-height:40px!important;min-width:40px!important;overflow:hidden!important;padding:0!important;transition:transform .15s,background .15s;width:40px!important}',
			'.ec-cw__send:before{content:"";display:block;width:22px;height:22px;background:currentColor;mask:url("data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 24 24%27 fill=%27none%27 stroke=%27black%27 stroke-width=%272%27 stroke-linecap=%27round%27 stroke-linejoin=%27round%27%3E%3Cpath d=%27m22 2-7 20-4-9-9-4Z%27/%3E%3Cpath d=%27M22 2 11 13%27/%3E%3C/svg%3E") center/contain no-repeat;-webkit-mask:url("data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 24 24%27 fill=%27none%27 stroke=%27black%27 stroke-width=%272%27 stroke-linecap=%27round%27 stroke-linejoin=%27round%27%3E%3Cpath d=%27m22 2-7 20-4-9-9-4Z%27/%3E%3Cpath d=%27M22 2 11 13%27/%3E%3C/svg%3E") center/contain no-repeat}',
			'.ec-cw__send:hover{transform:scale(1.08)}',
			'.ec-cw__send:active{transform:scale(.96)}',
			'.ec-cw__send:disabled{background:#e9edf5;color:#8a94a6;cursor:not-allowed;transform:none}',

			/* empty state */
			'.ec-cw__empty{align-items:center;color:#94a3b8;display:flex;flex-direction:column;font-size:13px;gap:8px;height:100%;justify-content:center;padding:20px;text-align:center}',
			'.ec-cw__empty-icon{font-size:32px;opacity:.4}',

		].join('');

		var mobileStyles = [
			'@media (max-width:640px){',
			'.ec-cw{bottom:0;right:0}',
			'.ec-cw__panel,.ec-cw.has-thread .ec-cw__panel{border-radius:0;bottom:0;height:100dvh;max-height:100dvh;overflow:hidden;position:fixed;right:0;width:100vw}',
			'.ec-cw.has-thread .ec-cw__panel{display:grid;grid-template-columns:1fr;grid-template-rows:1fr}',
			'.ec-cw.has-thread .ec-cw__body{display:block;height:100%;min-height:0;overflow:hidden}',
			'.ec-cw--admin:not(.is-thread) .ec-cw__panel{display:grid;grid-template-rows:1fr}',
			'.ec-cw--admin:not(.is-thread) .ec-cw__body{display:block;height:100%;min-height:0;overflow:hidden}',
			'.ec-cw--admin:not(.is-thread) .ec-cw__header{display:none!important}',
			'.ec-cw.has-thread .ec-cw__thread{display:none}',
			'.ec-cw.is-thread .ec-cw__list{display:none}',
			'.ec-cw.is-thread .ec-cw__panel{grid-template-rows:64px 1fr}',
			'.ec-cw--admin.is-thread .ec-cw__header{display:flex!important}',
			'.ec-cw.is-thread .ec-cw__thread{display:flex;height:100%;min-height:0;overflow:hidden}',
			'.ec-cw.is-thread .ec-cw__thread>.ec-cw__back{display:none!important}',
			'.ec-cw--admin.is-thread .ec-cw__header-back{display:flex}',
			'.ec-cw--admin.is-thread.has-thread .ec-cw__header{align-items:center;border-radius:0;display:grid!important;grid-column:1;grid-row:1;grid-template-columns:44px 44px minmax(0,1fr) 44px;height:64px;padding:0 18px 0 14px}',
			'.ec-cw--admin.is-thread.has-thread .ec-cw__header-back{grid-column:1;justify-self:center}',
			'.ec-cw--admin.is-thread.has-thread .ec-cw__header-customer-avatar{grid-column:2;justify-content:center;margin:0}',
			'.ec-cw--admin.is-thread.has-thread .ec-cw__header-main{grid-column:3;min-width:0}',
			'.ec-cw--admin.is-thread.has-thread .ec-cw__header-actions{gap:0;grid-column:4;justify-self:center;margin-left:0}',
			'.ec-cw--admin.is-thread.has-thread .ec-cw__header-actions button{height:44px;width:44px}',
			'.ec-cw--admin.is-thread.has-thread .ec-cw__header-title{font-size:16px;line-height:1.2}',
			'.ec-cw--admin.is-thread.has-thread .ec-cw__header-subtitle{font-size:12px;line-height:1}',
			'.ec-cw--admin .ec-cw__list{border-right:none;display:flex;flex-direction:column;grid-column:1;grid-row:1;height:100%;max-height:100%;min-height:0;overflow:hidden;padding:0}',
			'.ec-cw--admin .ec-cw__list-chrome{display:flex;flex:0 0 auto;flex-direction:column;min-height:0;position:relative;z-index:4}',
			'.ec-cw--admin .ec-cw__list-scroll{flex:1 1 0;min-height:0;overflow-x:hidden;overflow-y:auto;overscroll-behavior:contain;padding:8px 0;scrollbar-gutter:stable;-webkit-overflow-scrolling:touch}',
			'.ec-cw--admin .ec-cw__list-header,.ec-cw--admin.has-thread .ec-cw__list-header{align-items:center;background:var(--chat-primary);color:#fff;display:flex;font-size:16px;font-weight:700;gap:10px;height:64px;letter-spacing:0;padding:0 18px;position:relative;top:auto;text-transform:none;z-index:auto}',
			'.ec-cw--admin .ec-cw__list-header:before{content:none}',
			'.ec-cw--admin .ec-cw__list-header .ec-cw__header-customer-avatar{flex:0 0 auto;justify-content:center;margin:0}',
			'.ec-cw--admin .ec-cw__list-title{flex:1;min-width:0;overflow:hidden;padding-left:0;text-overflow:ellipsis;white-space:nowrap}',
			'.ec-cw--admin:not(.has-thread) .ec-cw__list-header--empty{display:flex}',
			'.ec-cw--admin .ec-cw__list-header--empty .ec-cw__header-customer-avatar{display:flex}',
			'.ec-cw--admin .ec-cw__list-close{align-items:center;background:transparent;border:0;border-radius:50%;color:#fff;cursor:pointer;display:flex;flex:0 0 44px;height:44px;justify-content:center;margin-left:auto;width:44px}',
			'.ec-cw--admin .ec-cw__list-close:hover{background:rgba(255,255,255,.12)}',
			'.ec-cw--admin .ec-cw__list-search,.ec-cw--admin.has-thread .ec-cw__list-search{position:relative;top:auto;z-index:auto}',
			'.ec-cw--admin .ec-cw__list-search:before,.ec-cw--admin.has-thread .ec-cw__list-search:before{transform:none}',
			'.ec-cw--admin .ec-cw__conv.is-active,.ec-cw--admin .ec-cw__conv.is-active:hover{background:transparent;box-shadow:none}',
			'.ec-cw--admin .ec-cw__conv.is-active:before{display:none}',
			'.ec-cw--admin.is-thread .ec-cw__messages{flex:1 1 0;min-height:0;overflow-x:hidden;overflow-y:auto;overscroll-behavior:contain;scrollbar-gutter:stable;-webkit-overflow-scrolling:touch}',
			'}'
		].join('');
		style.textContent = [desktopStyles, mobileStyles].join('');
		document.head.appendChild(style);
	}

	function renderToggleMarkup() {
		return [
			'<button type="button" class="ec-cw__toggle">',
			'<span class="ec-cw__toggle-icon">💬</span>',
			'<span>Hỗ trợ</span>',
			'<strong class="ec-cw__badge">0</strong>',
			'</button>'
		].join('');
	}

	function renderHeaderMarkup() {
		return [
			'<div class="ec-cw__header">',
			'<button type="button" class="ec-cw__header-back" title="Danh sách hội thoại" aria-label="Danh sách hội thoại">',
			'<svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>',
			'</button>',
			'<div class="ec-cw__header-customer-avatar"></div>',
			'<div class="ec-cw__header-main">',
			'<div class="ec-cw__header-title">' + escapeHtml(getHeaderTitle(getConversation(state.selectedConversationId))) + '</div>',
			'<div class="ec-cw__header-subtitle"><span class="ec-cw__header-status-text">' + escapeHtml(config.status || config.subtitle) + '</span><span class="ec-cw__header-admin-avatars"></span></div>',
			'<div class="ec-cw__admin-participants-popover"></div>',
			'</div>',
			'<div class="ec-cw__header-actions">',
			'<button type="button" class="ec-cw__minimize" title="Thu nhỏ">',
			'<svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" d="M20 12H4"/></svg>',
			'</button>',
			'<button type="button" class="ec-cw__close" title="Đóng">',
			'<svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>',
			'</button>',
			'</div>',
			'</div>'
		].join('');
	}

	function renderReplyMarkup() {
		return [
			'<div class="ec-cw__reply">',
			'<label class="ec-cw__upload" title="Đính kèm ảnh">',
			'<input type="file" class="ec-cw__image-upload" accept="image/*">',
			'<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>',
			'</label>',
			'<textarea class="ec-cw__reply-text" placeholder="Nhập tin nhắn…"></textarea>',
			'<button type="button" class="ec-cw__send" aria-label="Gửi tin nhắn"></button>',
			'</div>'
		].join('');
	}

	function renderThreadMarkup() {
		return [
			'<div class="ec-cw__thread">',
			'<button type="button" class="ec-cw__back">',
			'<svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" d="M15 19l-7-7 7-7"/></svg>',
			'Danh sách hội thoại',
			'</button>',
			'<div class="ec-cw__reconnect-status" aria-live="polite" aria-hidden="true">',
			'<svg class="ec-cw__reconnect-icon" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v6h6M20 20v-6h-6"/><path stroke-linecap="round" stroke-linejoin="round" d="M20 9a8 8 0 0 0-13.66-3.66L4 7.68M4 15a8 8 0 0 0 13.66 3.66L20 16.32"/></svg>',
			'<span class="ec-cw__reconnect-label">Đang kết nối lại...</span>',
			'</div>',
			'<div class="ec-cw__customer"></div>',
			'<div class="ec-cw__messages"></div>',
			'<div class="ec-cw__img-preview"></div>',
			renderReplyMarkup(),
			'</div>'
		].join('');
	}

	function renderConversationListMarkup() {
		return '<div class="ec-cw__list"></div>';
	}

	function buildMarkup() {
		if (document.getElementById(WIDGET_ID)) return;
		var root = document.createElement('div');
		root.id = WIDGET_ID;
		root.className = 'ec-cw ec-cw--' + config.role + (canUploadImages() ? '' : ' ec-cw--no-upload');
		root.innerHTML = [
			renderToggleMarkup(),
			'<div class="ec-cw__panel" aria-hidden="true">',
			renderHeaderMarkup(),
			'<div class="ec-cw__body">',
			renderThreadMarkup(),
			renderConversationListMarkup(),
			'</div>',
			'</div>'
		].join('');
		document.body.appendChild(root);
	}

	function renderBadge() {
		var badge = qs('.ec-cw__badge');
		if (!badge) return;
		var total = getUnreadTotal();
		badge.textContent = total;
		badge.style.display = total > 0 ? 'flex' : 'none';
	}

	function renderTickSvg(status) {
		var normalized = status === true ? 'read' : (status || 'sent');
		if (normalized === 'failed') {
			return '<span class="ec-cw__receipt-error-mark">!</span>';
		}
		if (normalized === 'sending') {
			return '<span class="ec-cw__receipt-sending-mark">…</span>';
		}
		var isRead = normalized === 'read';
		var isDelivered = normalized === 'delivered';
		if (isRead) {
			return [
				'<svg width="16" height="12" viewBox="0 0 16 12" fill="none" xmlns="http://www.w3.org/2000/svg">',
				'<path class="tick-first" d="M1 6l3.5 3.5L11 2" stroke="var(--chat-success)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>',
				'<path class="tick-second" d="M5 6l3.5 3.5L15 2" stroke="var(--chat-success)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>',
				'</svg>'
			].join('');
		}
		if (isDelivered) {
			return [
				'<svg width="16" height="12" viewBox="0 0 16 12" fill="none" xmlns="http://www.w3.org/2000/svg">',
				'<path class="tick-first" d="M1 6l3.5 3.5L11 2" stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>',
				'<path class="tick-second" d="M5 6l3.5 3.5L15 2" stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>',
				'</svg>'
			].join('');
		}
		return [
			'<svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">',
			'<path class="tick-first" d="M1 6l3.5 3.5L11 2" stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>',
			'</svg>'
		].join('');
	}

	function renderConvTick(message) {
		var status = getReceiptStatus(message);
		var labelText = getReceiptLabel(message, status);
		var tickClass = status === 'read' ? 'ec-cw__conv-tick--seen' : 'ec-cw__conv-tick--sent';
		return '<span class="ec-cw__conv-tick ' + tickClass + '" title="' + escapeHtml(labelText) + '">' + renderTickSvg(status) + '</span>';
	}

	function renderConversationTypingPreview(typing) {
		var label = typing && typing.role === 'admin'
			? ((typing.adminName || 'Tư vấn viên') + ' đang nhập')
			: 'Đang nhập';
		return [
			'<span class="ec-cw__conv-preview is-typing">',
			'<span>' + escapeHtml(label) + '</span>',
			'<span class="ec-cw__conv-typing-dots"><span></span><span></span><span></span></span>',
			'</span>'
		].join('');
	}

	function renderConversationPreview(previewData, fallbackText, previewClass) {
		previewData = previewData || {};
		var titleText = String(previewData.text || fallbackText || '').trim();
		if (previewData.senderType === 'staff' && previewData.content) {
			var senderName = truncateText(previewData.senderName || 'admin', 18);
			var contentLimit = Math.max(8, 42 - senderName.length - 2);
			return [
				'<div class="ec-cw__conv-preview' + previewClass + '" title="' + escapeHtml(titleText) + '">',
				'<span class="ec-cw__conv-preview-admin">' + escapeHtml(senderName) + ':</span> ',
				'<span class="ec-cw__conv-preview-message">' + escapeHtml(truncateText(previewData.content, contentLimit)) + '</span>',
				'</div>'
			].join('');
		}
		return '<div class="ec-cw__conv-preview' + previewClass + '" title="' + escapeHtml(titleText) + '">' + escapeHtml(truncateText(titleText, 42)) + '</div>';
	}

	function renderListChrome() {
		var headerText = 'Danh sách';
		var closeButton = '<button type="button" class="ec-cw__list-close ec-cw__close" title="Đóng" aria-label="Đóng"><svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg></button>';
		var header = state.selectedConversationId
			? '<div class="ec-cw__list-header"><span class="ec-cw__list-title">' + headerText + '</span>' + closeButton + '</div>'
			: '<div class="ec-cw__list-header ec-cw__list-header--empty"><span class="ec-cw__list-title">' + headerText + '</span>' + closeButton + '</div>';
		return [
			header,
			'<div class="ec-cw__list-search">',
			'<input type="search" class="ec-cw__list-search-input" placeholder="Tìm kiếm hội thoại..." value="' + escapeHtml(state.conversationSearch) + '">',
			'</div>'
		].join('');
	}

	function setListContent(list, chrome, bodyHtml) {
		var currentScroll = qs('.ec-cw__list-scroll', list);
		var scrollTop = currentScroll ? currentScroll.scrollTop : 0;
		list.innerHTML = '<div class="ec-cw__list-chrome">' + chrome + '</div>' +
			'<div class="ec-cw__list-scroll">' + bodyHtml + '</div>';
		var nextScroll = qs('.ec-cw__list-scroll', list);
		if (nextScroll) nextScroll.scrollTop = scrollTop;
	}

	function renderList() {
	var list = qs('.ec-cw__list');
	if (!list) return;
	if (!state.selectedConversationId) updateHeader(null);
	if (isCustomerMode()) {
		list.innerHTML = '';
		renderBadge();
		return;
	}

	var chrome = renderListChrome();
	var bodyHtml;

	if (!data.conversations.length) {
		bodyHtml = [
			'<div class="ec-cw__list-empty">',
			'<div class="ec-cw__list-empty-icon"></div>',
			'<div class="ec-cw__list-empty-title">Chưa có hội thoại mới</div>',
			'<div class="ec-cw__list-empty-text">Các cuộc trò chuyện từ khách hàng sẽ xuất hiện tại đây.</div>',
			'</div>'
		].join('');

		setListContent(list, chrome, bodyHtml);
		renderBadge();
		return;
	}

	var searchText = String(state.conversationSearch || '').trim().toLowerCase();
	var conversations = searchText
		? data.conversations.filter(function (conversation) {
			return [
				conversation.customerName,
				conversation.phone,
				conversation.id,
				getConversationPreviewText(conversation)
			].join(' ').toLowerCase().indexOf(searchText) !== -1;
		})
		: data.conversations;
	conversations = sortConversationsByActivity(conversations);

	if (!conversations.length) {
		bodyHtml = [
			'<div class="ec-cw__list-empty">',
			'<div class="ec-cw__list-empty-icon"></div>',
			'<div class="ec-cw__list-empty-title">Không tìm thấy hội thoại</div>',
			'<div class="ec-cw__list-empty-text">Thử tìm bằng số điện thoại hoặc nội dung gần nhất.</div>',
			'</div>'
		].join('');

		setListContent(list, chrome, bodyHtml);
		renderBadge();
		return;
	}

	bodyHtml = conversations.map(function (conversation) {
		var activeClass = conversation.id === state.selectedConversationId ? ' is-active' : '';
		var unread = conversation.unreadCount > 0
			? '<span class="ec-cw__unread">' + conversation.unreadCount + '</span>'
			: '';
		var initials = getCustomerAvatarText();
		var colors = getAvatarColor(getCustomerAvatarColorKey(conversation));
		var latestMessageItem = getLatestNonSystemMessage(conversation.id);
		var previewData = getConversationPreviewData(conversation);
		var previewText = previewData.text;
		var typing = state.typingByConversation[conversation.id];
		var hasLatestMessage = !!String(previewText || '').trim();
		var isSupportStatus = String(previewText || '').trim() === 'Đang được hỗ trợ';
		var emptyClass = hasLatestMessage ? '' : ' ec-cw__conv--empty';
		var previewClass = hasLatestMessage && !isSupportStatus ? '' : ' is-empty';
			var previewHtml = typing
				? renderConversationTypingPreview(typing)
				: renderConversationPreview(previewData, hasLatestMessage ? previewText : 'Chưa có tin nhắn', previewClass);
			var timeText = formatTelegramListTime(
				(latestMessageItem && latestMessageItem.createdAtRaw) || conversation.updatedAtRaw,
				(latestMessageItem && latestMessageItem.createdAt) || conversation.updatedAt
			);

		var phoneText = getConversationPhone(conversation);
		var nameText = String(conversation.customerName || '').trim();
		var displayName = phoneText ? (phoneText) : (nameText || 'Khách hàng');

		var tickHtml = '';
		if (latestMessageItem && latestMessageItem.senderType === 'staff' && !conversation.unreadCount) {
			tickHtml = renderConvTick(latestMessageItem);
		}

		return [
			'<button type="button" class="ec-cw__conv' + emptyClass + activeClass + '" data-conversation-id="' + escapeHtml(conversation.id) + '">',
			'<div class="ec-cw__conv-avatar" style="background:' + colors.bg + ';color:' + colors.text + '">' + initials + '</div>',
			'<div class="ec-cw__conv-body">',
			'<div class="ec-cw__conv-top">',
			'<span class="ec-cw__conv-name">' + escapeHtml(displayName) + '</span>',
			'<span class="ec-cw__conv-time">' + escapeHtml(timeText) + '</span>',
			'</div>',
			'<div class="ec-cw__conv-preview-row">',
			previewHtml,
			unread || tickHtml,
			'</div>',
			'</div>',
			'</button>'
		].join('');
	}).join('');

	setListContent(list, chrome, bodyHtml);
	renderBadge();
}

	function getMessageActorKey(message) {
		if (!message) return '';
		if (message.senderType === 'staff') {
			return 'staff:' + (message.adminId || message.adminName || message.senderName || 'staff');
		}
		return String(message.senderType || 'customer');
	}

	function shouldHideMessageMeta(message, index, messages) {
		if (!message || message.senderType === 'system' || message.seenAt) return false;
		var next = messages[index + 1];
		if (!next || next.senderType === 'system') return false;
		return getMessageActorKey(message) === getMessageActorKey(next)
			&& String(message.createdAt || '') === String(next.createdAt || '');
	}

	function isPresenceSystemMessage(message) {
		if (!message || message.senderType !== 'system') return false;
		var code = message.code || '';
		var content = String(message.content || message.text || '');
		return code === 'peer_admin_joined' ||
			code === 'peer_admin_left' ||
			code === 'customer_joined' ||
			code === 'customer_disconnected' ||
			content === 'Khách hàng đã tham gia cuộc trò chuyện' ||
			content === 'Khách hàng đã ngắt kết nối' ||
			/đã tham gia cuộc trò chuyện$/.test(content) ||
			/đã rời cuộc trò chuyện$/.test(content);
	}

	function isOwnMessage(message) {
		var senderType = message && message.senderType ? message.senderType : 'customer';
		if (isCustomerMode()) return senderType === 'customer';
		if (senderType !== 'staff') return false;
		if (message.sessionId && message.sessionId === state.sessionId) return true;
		if (message.adminId && message.adminId === getAdminId()) return true;
		return false;
	}

	function shouldRenderReceipt(message) {
		var senderType = message && message.senderType ? message.senderType : 'customer';
		return isCustomerMode() ? isOwnMessage(message) : senderType === 'staff';
	}

	function getReceiptStatus(message) {
		if (message.seenAt) return 'read';
		if (message.deliveredAt || message.receivedAt) return 'delivered';
		if (message.deliveryState === 'failed') return 'failed';
		if (message.deliveryState === 'sending') return 'sending';
		return 'sent';
	}

	function getReceiptLabel(message, status) {
		if (status === 'failed') return 'Gửi lỗi - bấm để thử lại';
		if (status === 'sending') return 'Đang gửi';
		if (status === 'read') return 'Đã xem';
		if (status === 'delivered') return 'Đã nhận';
		return 'Đã gửi';
	}

	function renderMessageMeta(message, hideMeta, insideBubble) {
		if (shouldRenderReceipt(message)) {
			var status = getReceiptStatus(message);
			var labelText = getReceiptLabel(message, status);
			var tickClass = status === 'read' ? 'ec-cw__receipt-tick--seen' : (status === 'failed' ? 'ec-cw__receipt-tick--failed' : 'ec-cw__receipt-tick--sent');
			var wrapperClass = insideBubble ? ' ec-cw__receipt--bubble' : '';
			var retryAttr = status === 'failed' ? ' data-retry="1" role="button" tabindex="0"' : '';
			return [
				'<span class="ec-cw__receipt' + wrapperClass + '" data-msg-id="' + escapeHtml(message.id) + '" data-tooltip="' + escapeHtml(labelText) + '"' + retryAttr + '>',
				'<span class="ec-cw__receipt-time">' + escapeHtml(message.createdAt || '') + '</span>',
				'<span class="ec-cw__receipt-tick ' + tickClass + '">' + renderTickSvg(status) + '</span>',
				'</span>'
			].join('');
		}
		if (insideBubble) {
			var bubbleTime = message.createdAt || '';
			return bubbleTime
				? '<span class="ec-cw__receipt ec-cw__receipt--bubble"><span class="ec-cw__receipt-time">' + escapeHtml(bubbleTime) + '</span></span>'
				: '';
		}
		if (hideMeta) return '';
		var timeText = message.createdAt || '';
		return timeText ? '<span class="ec-cw__time-s">' + escapeHtml(timeText) + '</span>' : '';
	}

	function renderMessage(message, isNew, hideMeta) {
		var newClass = isNew ? ' is-new' : '';
		if (message.senderType === 'system') {
			return [
				'<div class="ec-cw__msg ec-cw__msg--system' + newClass + '" data-thread-message-id="' + escapeHtml(message.id || '') + '">',
				'<div class="ec-cw__sys-note">',
				escapeHtml(toPlainText(message.content)),
				'<span class="ec-cw__time-s">' + escapeHtml(message.createdAt) + '</span>',
				'</div>',
				'</div>'
			].join('');
		}

		var view = getMessageView(message);
		var imageSrc = message.imageData || message.imageUrl || '';
		var image = imageSrc
			? '<img src="' + escapeHtml(imageSrc) + '" alt="' + escapeHtml(message.imageName || 'Ảnh') + '" style="border-radius:6px;margin-top:6px;max-width:100%">'
			: '';
		var avatar = view.avatarUrl
			? '<img src="' + escapeHtml(view.avatarUrl) + '" alt="' + escapeHtml(view.label) + '">'
			: view.avatarText;

		var bubbleReceipt = renderMessageMeta(message, false, true);
		var content = escapeHtml(toPlainText(message.content)) + (image ? '' : bubbleReceipt);
		var imageReceipt = image ? bubbleReceipt : '';
		var seenTrackAttr = isCustomerMode() && message.senderType === 'staff' && message.id
			? ' data-message-id="' + escapeHtml(message.id) + '"'
			: '';

		return [
			'<div class="ec-cw__msg ec-cw__msg--' + escapeHtml(view.visualType) + newClass + '" data-thread-message-id="' + escapeHtml(message.id || '') + '"' + seenTrackAttr + '>',
			'<div class="ec-cw__msg-avatar" style="background:' + view.avatarBg + ';color:' + view.avatarColor + '">' + avatar + '</div>',
			'<div class="ec-cw__msg-stack">',
			'<div class="ec-cw__bubble">',
			'<span class="ec-cw__label">' + escapeHtml(view.label) + '</span>',
			'<div class="ec-cw__content">' + content + '</div>',
			image,
			imageReceipt,
			'</div>',
			'</div>',
			'</div>'
		].join('');
	}

	function renderThread(options) {
		var root = document.getElementById(WIDGET_ID);
		var conversation = getConversation(state.selectedConversationId);
		var customer = qs('.ec-cw__customer');
		var messages = qs('.ec-cw__messages');
		var replyText = qs('.ec-cw__reply-text');
		if (!root || !customer || !messages || !replyText) return;

		if (!conversation) {
			updateHeader(null);
			if (isCustomerMode()) {
				root.classList.remove('has-thread', 'is-thread');
				return;
			}
			root.classList.remove('is-thread');
			root.classList.add('has-thread');
			customer.innerHTML = '';
			messages.innerHTML = '<div class="ec-cw__empty"><div class="ec-cw__empty-icon">💬</div><span>Chọn một hội thoại để tham gia.</span></div>';
			replyText.value = '';
			replyText.placeholder = 'Nhập tin nhắn…';
			resizeReplyText(replyText);
			replyText.disabled = true;
			updateSendButtonState();
			return;
		}

		conversation.unreadCount = 0;
		if (isCustomerMode()) {
			joinRealtimeConversation(conversation.id);
		}
		root.classList.add('has-thread', 'is-thread');

		var colors = getAvatarColor(getCustomerAvatarColorKey(conversation));
		updateHeader(conversation);

		// Show phone number if available
		var phoneDisplay = getConversationPhone(conversation);
		var phoneField = phoneDisplay
			? '<span class="ec-cw__customer-field">📞 <strong>+' + escapeHtml(phoneDisplay) + '</strong></span>'
			: '';

		customer.innerHTML = [
			'<div class="ec-cw__customer-top">',
			'<div class="ec-cw__customer-avatar" style="background:' + colors.bg + ';color:' + colors.text + '">' + getCustomerAvatarText() + '</div>',
			'<div>',
			'<div class="ec-cw__customer-name">' + escapeHtml(conversation.customerName) + '</div>',
			'</div>',
			'</div>',
			'<div class="ec-cw__customer-meta">',
			phoneField,
			'</div>'
		].join('');

		var threadMessages = getMessages(conversation.id).filter(function (message) {
			return !isPresenceSystemMessage(message);
		});
		var renderKey = getThreadRenderKey(conversation.id, threadMessages);
		var draft = state.drafts[conversation.id] || '';
		if (!options || !options.force) {
			if (state.threadRenderKey === renderKey && messages.getAttribute('data-conversation-id') === conversation.id) {
				replyText.disabled = false;
				replyText.placeholder = 'Nhập tin nhắn…';
				if (replyText.value !== draft) {
					replyText.value = draft;
					resizeReplyText(replyText);
				}
				updateReconnectUi();
				updateHeader(conversation);
				updateSendButtonState();
				renderList();
				settleThreadView(options);
				return;
			}
		}
		state.threadRenderKey = renderKey;
		messages.setAttribute('data-conversation-id', conversation.id);
		messages.classList.add('is-settling');
		messages.innerHTML = renderMessageThread(threadMessages) || '<div class="ec-cw__empty"><div class="ec-cw__empty-icon">💬</div><span>Chưa có tin nhắn.</span></div>';
		replyText.disabled = false;
		replyText.placeholder = 'Nhập tin nhắn…';
		replyText.value = draft;
		resizeReplyText(replyText);
		updateReconnectUi();
		updateSendButtonState();
		renderList();
		settleThreadView(options);
		if (isCustomerMode()) {
			setTimeout(checkVisibleCustomerSeenMessages, 120);
		}
	}

	function getThreadMessageElement(messagesEl, messageId) {
		if (!messagesEl || !messageId) return null;
		var nodes = qsa('.ec-cw__msg[data-thread-message-id]', messagesEl);
		for (var i = 0; i < nodes.length; i++) {
			if (nodes[i].getAttribute('data-thread-message-id') === String(messageId)) return nodes[i];
		}
		return null;
	}

	function removeLeadingDividerForMessage(messagesEl, message) {
		if (!messagesEl || !message || !message.id) return;
		var node = getThreadMessageElement(messagesEl, message.id);
		if (!node) return;
		var previous = node.previousElementSibling;
		if (previous && previous.classList.contains('ec-cw__date-divider') && previous.getAttribute('data-date-key') === getMessageDateKey(message)) {
			previous.remove();
		}
	}

	function renderOlderMessageBatch(olderMessages, firstExistingMessage) {
		var visibleOlder = (olderMessages || []).filter(function (message) {
			return message && !isPresenceSystemMessage(message);
		}).sort(compareMessagesByTime);
		if (!visibleOlder.length) return '';

		var context = firstExistingMessage ? visibleOlder.concat([firstExistingMessage]) : visibleOlder;
		var lastDateKey = '';
		return visibleOlder.map(function (message, index) {
			var dateKey = getMessageDateKey(message);
			var shouldRenderDivider = dateKey && dateKey !== lastDateKey && (lastDateKey || !isTodayMessageDate(message));
			var divider = shouldRenderDivider ? renderDateDivider(message) : '';
			if (dateKey) lastDateKey = dateKey;
			return divider + renderMessage(message, false, shouldHideMessageMeta(message, index, context));
		}).join('');
	}

	function prependOlderMessagesToThread(conversationId, olderMessages, options) {
		var messages = qs('.ec-cw__messages');
		if (!messages || !isSameConversationId(state.selectedConversationId, conversationId)) return false;
		var opts = options || {};
		var firstExistingMessage = opts.firstExistingMessage || null;
		var html = renderOlderMessageBatch(olderMessages, firstExistingMessage);
		if (!html) return false;

		var empty = qs('.ec-cw__empty', messages);
		if (empty) messages.innerHTML = '';
		messages.insertAdjacentHTML('afterbegin', html);
		if (firstExistingMessage && olderMessages.some(function (message) {
			return getMessageDateKey(message) && getMessageDateKey(message) === getMessageDateKey(firstExistingMessage);
		})) {
			removeLeadingDividerForMessage(messages, firstExistingMessage);
		}

		var threadMessages = getMessages(conversationId).filter(function (message) {
			return !isPresenceSystemMessage(message);
		});
		state.threadRenderKey = getThreadRenderKey(conversationId, threadMessages);
		messages.setAttribute('data-conversation-id', conversationId);
		window.requestAnimationFrame(function () {
			messages.scrollTop = Math.max(0, messages.scrollHeight - (opts.previousHeight || 0) + (opts.previousTop || 0));
			messages.classList.remove('is-settling');
		});
		return true;
	}

	function renderImagePreview() {
		var preview = qs('.ec-cw__img-preview');
		if (!preview) return;
		if (!state.selectedImage) {
			preview.classList.remove('is-visible');
			preview.innerHTML = '';
			updateSendButtonState();
			return;
		}
		preview.classList.add('is-visible');
		preview.innerHTML = [
			'<div class="ec-cw__img-chip">',
			'<img src="' + state.selectedImage.data + '" alt="' + escapeHtml(state.selectedImage.name) + '">',
			'<span>' + escapeHtml(state.selectedImage.name) + '</span>',
			'<button type="button" class="ec-cw__remove-image">×</button>',
			'</div>'
		].join('');
		updateSendButtonState();
	}

	function updateSendButtonState() {
		var button = qs('.ec-cw__send');
		var replyText = qs('.ec-cw__reply-text');
		if (!button) return;
		var hasText = replyText && replyText.value.trim().length > 0;
		button.disabled = (replyText && replyText.disabled) || (!hasText && !state.selectedImage);
	}

	function resizeReplyText(textarea) {
		if (!textarea) return;
		var maxHeight = 104;
		textarea.style.height = '40px';
		var nextHeight = Math.min(textarea.scrollHeight, maxHeight);
		textarea.style.height = Math.max(40, nextHeight) + 'px';
		textarea.style.overflowY = textarea.scrollHeight > maxHeight ? 'auto' : 'hidden';
	}

	function scrollThreadToBottom(smooth) {
		var messages = qs('.ec-cw__messages');
		if (!messages) return;
		if (smooth && typeof messages.scrollTo === 'function') {
			messages.scrollTo({ top: messages.scrollHeight, behavior: 'smooth' });
			return;
		}
		messages.scrollTop = messages.scrollHeight;
	}

	function settleThreadView(options) {
		var opts = options || {};
		var messages = qs('.ec-cw__messages');
		var revealMessages = function () {
			if (messages) messages.classList.remove('is-settling');
		};
		if (opts.preserveScroll) {
			window.requestAnimationFrame(function () {
				if (messages) {
					var previousHeight = opts.preserveScroll.height || 0;
					var previousTop = opts.preserveScroll.top || 0;
					messages.scrollTop = Math.max(0, messages.scrollHeight - previousHeight + previousTop);
				}
				revealMessages();
			});
			return;
		}
		if (opts.smooth) {
			window.requestAnimationFrame(function () {
				scrollThreadToBottom(true);
				revealMessages();
				if (opts.focus) {
					var replyText = qs('.ec-cw__reply-text');
					if (replyText) replyText.focus();
				}
			});
			return;
		}
		window.requestAnimationFrame(function () {
			scrollThreadToBottom(false);
			revealMessages();
			if (opts.focus) {
				var replyText = qs('.ec-cw__reply-text');
				if (replyText) replyText.focus();
			}
		});
	}

	function appendMessageToThread(message, options) {
		var messages = qs('.ec-cw__messages');
		if (!messages) return false;
		var empty = qs('.ec-cw__empty', messages);
		if (empty) messages.innerHTML = '';

		var threadMessages = getMessages(message.conversationId).filter(function (item) {
			return !isPresenceSystemMessage(item);
		});
		var currentIndex = threadMessages.length - 1;
		if (currentIndex > 0 && shouldHideMessageMeta(threadMessages[currentIndex - 1], currentIndex - 1, threadMessages)) {
			var timeNodes = qsa('.ec-cw__msg:not(.ec-cw__msg--system) .ec-cw__time-s', messages);
			if (timeNodes.length) timeNodes[timeNodes.length - 1].remove();
		}
		var previousMessage = currentIndex > 0 ? threadMessages[currentIndex - 1] : null;
		var shouldRenderDivider = getMessageDateKey(message) &&
			(previousMessage ? getMessageDateKey(message) !== getMessageDateKey(previousMessage) : !isTodayMessageDate(message));
		var divider = shouldRenderDivider
			? renderDateDivider(message)
			: '';
		messages.insertAdjacentHTML('beforeend', divider + renderMessage(message, true, shouldHideMessageMeta(message, currentIndex, threadMessages)));
		state.threadRenderKey = getThreadRenderKey(message.conversationId, threadMessages);
		messages.setAttribute('data-conversation-id', message.conversationId);
		settleThreadView(options || { smooth: true });
		if (isCustomerMode() && message.senderType === 'staff') {
			setTimeout(checkVisibleCustomerSeenMessages, 120);
		}
		return true;
	}

	// ── Seen: customer has read admin messages → update tick to "read" ──────────
	// Server sends { type:'seen', conversationId, lastReadMessageId, timestamp } to admin tabs.
	function rememberPendingReceipt(conversationId, type, messageId, timestamp) {
		if (!conversationId || !type || !messageId) return;
		var key = String(conversationId);
		if (!state.pendingReceiptsByConversation[key]) state.pendingReceiptsByConversation[key] = {};
		state.pendingReceiptsByConversation[key][type] = {
			messageId: messageId,
			timestamp: timestamp
		};
	}

	function applyPendingReceipts(conversationId, options) {
		if (!conversationId) return;
		var key = String(conversationId);
		var pending = state.pendingReceiptsByConversation[key];
		if (!pending) return;
		var opts = Object.assign({ render: false, store: false }, options || {});
		if (pending.delivered && markStaffMessagesDeliveredUntilId(conversationId, pending.delivered.messageId, pending.delivered.timestamp, opts)) {
			delete pending.delivered;
		}
		if (pending.seen && markStaffMessagesSeenUntilId(conversationId, pending.seen.messageId, pending.seen.timestamp, opts)) {
			delete pending.seen;
		}
		if (!pending.delivered && !pending.seen) delete state.pendingReceiptsByConversation[key];
	}

	function markStaffMessagesSeenUntilId(conversationId, lastReadMessageId, timestamp, options) {
		if (!conversationId || !lastReadMessageId) return false;
		var opts = Object.assign({ render: true, store: true }, options || {});

		var seenAt = formatTimestamp(timestamp) || formatCurrentTime();
		var conversationMessages = getMessages(conversationId);
		var targetIndex = -1;

		for (var i = 0; i < conversationMessages.length; i++) {
			if (conversationMessages[i].id === lastReadMessageId) {
				targetIndex = i;
				break;
			}
		}

		if (targetIndex === -1) {
			if (opts.store !== false) rememberPendingReceipt(conversationId, 'seen', lastReadMessageId, timestamp);
			return false;
		}
		if (conversationMessages[targetIndex].senderType !== 'staff') return false;

		state.seenByConversation[conversationId] = seenAt;

		for (var j = 0; j <= targetIndex; j++) {
			var message = conversationMessages[j];
			if (message.senderType === 'staff') {
				message.seenAt = seenAt;
				message.deliveryState = 'sent';
				clearPendingMessageTimer(message.id);
			}
		}

		if (opts.render !== false) {
			if (isSameConversationId(state.selectedConversationId, conversationId)) {
				renderThread();
			}
			renderList();
		}
		return true;
	}

	function markStaffMessagesDeliveredUntilId(conversationId, lastDeliveredMessageId, timestamp, options) {
		if (!conversationId || !lastDeliveredMessageId) return false;
		var opts = Object.assign({ render: true, store: true }, options || {});

		var deliveredAt = formatTimestamp(timestamp) || formatCurrentTime();
		var conversationMessages = getMessages(conversationId);
		var targetIndex = -1;

		for (var i = 0; i < conversationMessages.length; i++) {
			if (conversationMessages[i].id === lastDeliveredMessageId) {
				targetIndex = i;
				break;
			}
		}

		if (targetIndex === -1) {
			if (opts.store !== false) rememberPendingReceipt(conversationId, 'delivered', lastDeliveredMessageId, timestamp);
			return false;
		}
		if (conversationMessages[targetIndex].senderType !== 'staff') return false;

		for (var j = 0; j <= targetIndex; j++) {
			var message = conversationMessages[j];
			if (message.senderType === 'staff' && !message.seenAt) {
				message.deliveredAt = deliveredAt;
				message.delivered = true;
				message.deliveryState = 'sent';
				clearPendingMessageTimer(message.id);
			}
		}

		if (opts.render !== false) {
			if (isSameConversationId(state.selectedConversationId, conversationId)) {
				renderThread();
			}
			renderList();
		}
		return true;
	}

	function getReconnectDelay(attempt) {
		return Math.min(config.realtimeReconnectMaxDelayMs || 15000, 1000 * Math.pow(2, Math.max(0, attempt))) + Math.floor(Math.random() * 250);
	}

	function scheduleSubscriberReconnect() {
		if (!isRealtimeEnabled() || isCustomerMode()) return;
		clearTimeout(state.subReconnectTimer);
		var attempt = state.subReconnectAttempt || 0;
		var delay = getReconnectDelay(attempt);
		state.subReconnectAttempt = attempt + 1;
		state.subReconnectTimer = setTimeout(connectSubscriber, delay);
	}

	function scheduleConversationReconnect(conversationId) {
		if (!isRealtimeEnabled() || !conversationId) return;
		if (!isCustomerMode() && !isSameConversationId(state.selectedConversationId, conversationId)) return;
		clearTimeout(state.conversationReconnectTimers[conversationId]);
		setRealtimeReconnecting(conversationId, true);
		var attempt = state.conversationReconnectAttempts[conversationId] || 0;
		var delay = getReconnectDelay(attempt);
		state.conversationReconnectAttempts[conversationId] = attempt + 1;
		state.conversationReconnectTimers[conversationId] = setTimeout(function () {
			delete state.conversationReconnectTimers[conversationId];
			joinRealtimeConversation(conversationId, { force: true });
		}, delay);
	}

	function clearConversationReconnect(conversationId) {
		clearTimeout(state.conversationReconnectTimers[conversationId]);
		delete state.conversationReconnectTimers[conversationId];
		delete state.conversationReconnectAttempts[conversationId];
	}

	function markSubscriberActivity() {
		state.lastSubscriberActivityAt = Date.now();
	}

	function markConversationActivity(conversationId) {
		if (!conversationId) return;
		state.conversationActivityAt[conversationId] = Date.now();
	}

	function isSocketOpen(ws) {
		return !!(ws && ws.readyState === WebSocket.OPEN);
	}

	function getReconnectKey(conversationId) {
		return 'conversation:' + conversationId;
	}

	function isConversationReconnecting(conversationId) {
		return !!(conversationId && state.realtimeReconnectingByKey[getReconnectKey(conversationId)]);
	}

	function isConversationReconnected(conversationId) {
		return !!(conversationId && state.realtimeReconnectedByKey[getReconnectKey(conversationId)]);
	}

	function isActiveThreadReconnecting() {
		return isConversationReconnecting(state.selectedConversationId);
	}

	function isActiveThreadReconnected() {
		return isConversationReconnected(state.selectedConversationId);
	}

	function setRealtimeReconnecting(conversationId, active) {
		if (!conversationId) return;
		var key = getReconnectKey(conversationId);
		clearTimeout(state.realtimeReconnectHideTimers[key]);
		delete state.realtimeReconnectHideTimers[key];
		if (active) {
			state.realtimeReconnectingByKey[key] = true;
			delete state.realtimeReconnectedByKey[key];
		} else {
			delete state.realtimeReconnectingByKey[key];
		}
		updateReconnectUi();
	}

	function setRealtimeReconnected(conversationId) {
		if (!conversationId) return;
		var key = getReconnectKey(conversationId);
		clearTimeout(state.realtimeReconnectHideTimers[key]);
		delete state.realtimeReconnectingByKey[key];
		state.realtimeReconnectedByKey[key] = true;
		updateReconnectUi();
		state.realtimeReconnectHideTimers[key] = setTimeout(function () {
			delete state.realtimeReconnectedByKey[key];
			delete state.realtimeReconnectHideTimers[key];
			updateReconnectUi();
		}, 1200);
	}

	function clearRealtimeReconnectState(conversationId) {
		if (!conversationId) return;
		var key = getReconnectKey(conversationId);
		clearTimeout(state.realtimeReconnectHideTimers[key]);
		delete state.realtimeReconnectHideTimers[key];
		delete state.realtimeReconnectingByKey[key];
		delete state.realtimeReconnectedByKey[key];
		updateReconnectUi();
	}

	function updateReconnectUi() {
		var root = document.getElementById(WIDGET_ID);
		if (!root) return;
		var thread = qs('.ec-cw__thread', root);
		var status = qs('.ec-cw__reconnect-status', root);
		var reconnecting = isActiveThreadReconnecting();
		var reconnected = isActiveThreadReconnected();

		if (thread) {
			thread.classList.toggle('is-reconnecting', reconnecting);
			thread.classList.toggle('is-reconnected', reconnected);
			thread.classList.toggle('is-reconnected-show', reconnected);
		}
		if (status) {
			status.setAttribute('aria-hidden', (reconnecting || reconnected) ? 'false' : 'true');
			var label = status.querySelector('.ec-cw__reconnect-label');
			if (label) label.textContent = reconnected ? 'Đã kết nối' : 'Đang kết nối lại...';
		}
		updateSendButtonState();
	}

	function reconnectSubscriberNow(reason) {
		if (!isRealtimeEnabled() || isCustomerMode()) return;
		debugLog('subscriber recovery', reason || 'reconnect');
		clearTimeout(state.subReconnectTimer);
		if (state.subWs) {
			state.subWs.onclose = null;
			try { state.subWs.close(); } catch (e) { }
			state.subWs = null;
		}
		state.lastSubscriberActivityAt = 0;
		connectSubscriber();
	}

	function reconnectConversationNow(conversationId, reason) {
		if (!isRealtimeEnabled() || !conversationId) return;
		if (!isCustomerMode() && !isSameConversationId(state.selectedConversationId, conversationId)) return;
		debugLog('conversation recovery', conversationId, reason || 'reconnect');
		setRealtimeReconnecting(conversationId, true);
		clearConversationReconnect(conversationId);
		var ws = state.conversationSockets[conversationId];
		if (ws) {
			ws.onclose = null;
			try { ws.close(); } catch (e) { }
		}
		delete state.conversationSockets[conversationId];
		delete state.joinedConversations[conversationId];
		delete state.conversationActivityAt[conversationId];
		joinRealtimeConversation(conversationId, { force: true });
	}

	function refreshActiveRealtimeData() {
		if (state.selectedConversationId) {
			reconnectConversationNow(state.selectedConversationId, 'refresh');
		} else if (!isCustomerMode()) {
			reconnectSubscriberNow('refresh');
		}
	}

	function recoverRealtime(reason) {
		if (!isRealtimeEnabled() || config.autoConnect === false) return;
		if (document.visibilityState && document.visibilityState === 'hidden') return;
		var now = Date.now();
		if (now - (state.lastRecoveryAt || 0) < (config.realtimeRecoveryCooldownMs || 5000)) return;
		state.lastRecoveryAt = now;

		var didRecover = false;
		if (!isCustomerMode() && !isSocketOpen(state.subWs)) {
			reconnectSubscriberNow(reason || 'recovery');
			didRecover = true;
		}
		if (state.selectedConversationId && !isSocketOpen(state.conversationSockets[state.selectedConversationId])) {
			reconnectConversationNow(state.selectedConversationId, reason || 'recovery');
			didRecover = true;
		}
		if (didRecover && isCustomerMode()) setTimeout(checkVisibleCustomerSeenMessages, 250);
	}

	function checkRealtimeStaleness() {
		if (!isRealtimeEnabled() || config.autoConnect === false) return;
		if (document.visibilityState && document.visibilityState === 'hidden') return;
		var now = Date.now();
		var staleMs = config.realtimeStaleMs || 45000;

		if (!isCustomerMode() && state.subWs) {
			if (isSocketOpen(state.subWs) && state.lastSubscriberActivityAt && now - state.lastSubscriberActivityAt > staleMs) {
				reconnectSubscriberNow('stale');
			} else if (state.subWs.readyState === WebSocket.CLOSED || state.subWs.readyState === WebSocket.CLOSING) {
				reconnectSubscriberNow('closed-stale');
			}
		}

		var conversationId = state.selectedConversationId;
		var ws = conversationId ? state.conversationSockets[conversationId] : null;
		if (!conversationId || !ws) return;
		var lastActivity = state.conversationActivityAt[conversationId] || 0;
		if (isSocketOpen(ws) && lastActivity && now - lastActivity > staleMs) {
			reconnectConversationNow(conversationId, 'stale');
		} else if (ws.readyState === WebSocket.CLOSED || ws.readyState === WebSocket.CLOSING) {
			reconnectConversationNow(conversationId, 'closed-stale');
		}
	}

	function startRealtimeWatchdog() {
		if (state.realtimeWatchdogTimer || !isRealtimeEnabled()) return;
		state.realtimeWatchdogTimer = setInterval(checkRealtimeStaleness, config.realtimeWatchdogIntervalMs || 10000);
	}

	function connectSubscriber() {
		if (!isRealtimeEnabled() || isCustomerMode() || state.subWs) return;
		clearTimeout(state.subReconnectTimer);

		var ws = new WebSocket(config.wsUrl);
		state.subWs = ws;
		debugLog('subscriber connecting', config.wsUrl);

		ws.onopen = function () {
			debugLog('subscriber open');
			state.subReconnectAttempt = 0;
			markSubscriberActivity();
			ws.send(JSON.stringify({ type: 'subscribe', adminId: getAdminId() }));
		};

		ws.onmessage = function (event) {
			var msg;
			try { msg = JSON.parse(event.data); } catch (e) { return; }
			markSubscriberActivity();

			if (msg.type === 'ping') {
				ws.send(JSON.stringify({ type: 'pong' }));
			} else if (msg.type === 'subscribed') {
				debugLog('subscriber subscribed');
				setConnectionStatus('online');
			} else if (msg.type === 'notify') {
				debugLog('notify', msg);
				handleRealtimeNotify(msg);
			} else if (msg.type === 'claimed') {
				debugLog('claimed', msg);
				handleRealtimeClaimed(msg);
			} else if (msg.type === 'error') {
				setConnectionStatus('Lỗi kết nối');
			}
		};

		ws.onclose = function () {
			debugLog('subscriber closed');
			if (state.subWs === ws) state.subWs = null;
			setConnectionStatus('Đang kết nối lại');
			scheduleSubscriberReconnect();
		};

		ws.onerror = function () { };
	}

	function setConnectionStatus(text) {
		state.connectionStatus = text || config.status || config.subtitle;
		var subtitle = qs('.ec-cw__header-subtitle');
		var statusText = qs('.ec-cw__header-status-text');
		var conversation = getConversation(state.selectedConversationId);
		var displayText = getHeaderStatus(conversation);
		if (subtitle) subtitle.className = 'ec-cw__header-subtitle' + getHeaderPresenceClass(conversation);
		if (statusText) {
			statusText.textContent = displayText;
		} else if (subtitle) {
			subtitle.textContent = displayText;
		}
	}

	// ── notify: server tells subscriber a new/updated conversation exists ───────
	// Updated protocol: notify includes `phone` (raw digits) alongside conversationId (UUID).
	function handleRealtimeNotify(msg) {
		var phone = normalizePhoneLike(msg.phone || msg.customerPhone || '');
		var conversationId = msg.conversationId || phone;
		var preview = toPlainText(msg.preview || '');

		// Build display name from phone; conversation entry gets phone stored for later use
		var customerName = phone ? ('+' + phone) : 'Khách hàng';
		var conversation = ensureConversation(conversationId, {
			phone: phone,
			customerName: customerName,
			lastMessage: preview,
			lastMessageSenderType: msg.senderType || 'customer',
			lastMessageAdminName: msg.senderType === 'staff' ? normalizeStaffDisplayName(msg.adminName) : '',
			updatedAt: formatTimestamp(msg.timestamp) || formatCurrentTime(),
			updatedAtRaw: msg.timestamp || ''
		});
		moveConversationToTop(conversation.id);

		if (!isSameConversationId(conversationId, state.selectedConversationId) && !isSameConversationId(conversation.id, state.selectedConversationId) && preview && msg.senderType !== 'staff') {
			conversation.unreadCount = Number(conversation.unreadCount || 0) + 1;
		}

		// Session-takeover transition: the admin may still hold a socket for an old convId
		// that was just merged away (renamed to conversation.id). If the merged conversation
		// is now the selected one but has no socket, clean up stale sockets and join it so
		// the admin receives full messages without a limbo period.
		if (isSameConversationId(conversation.id, state.selectedConversationId) && !state.conversationSockets[conversation.id]) {
			_cleanupStaleSocketsExcept(conversation.id);
			joinRealtimeConversation(conversation.id);
		}

		renderList();
		renderBadge();
	}

	// Close and remove every conversation socket whose convId is no longer in data.conversations
	// (i.e. was merged away into another entry) except for the given keepId.
	function _cleanupStaleSocketsExcept(keepId) {
		Object.keys(state.conversationSockets).forEach(function (oldId) {
			if (oldId === keepId) return;
			if (getConversation(oldId)) return; // still valid
			var staleWs = state.conversationSockets[oldId];
			if (staleWs) { try { staleWs.close(); } catch (e) {} }
			delete state.conversationSockets[oldId];
			delete state.joinedConversations[oldId];
		});
	}

	function handleRealtimeClaimed(msg) {
		rememberAdminParticipant(msg.conversationId, msg);
		var conversation = ensureConversation(msg.conversationId, {
			claimedBy: msg.adminName || msg.adminId || 'Tư vấn viên'
		});
		if (!conversation.lastMessage) {
			conversation.lastMessage = 'Đang được hỗ trợ';
		}
		if (isSameConversationId(state.selectedConversationId, msg.conversationId)) {
			updateHeader(conversation);
		}
		renderList();
	}

	function joinRealtimeConversation(conversationId, options) {
		options = options || {};
		if (!isRealtimeEnabled() || !conversationId) return;
		if (!options.force && (state.joinedConversations[conversationId] || state.conversationSockets[conversationId])) return;

		var conversation = ensureConversation(conversationId);
		conversationId = conversation.id;
		if (options.force) {
			delete state.joinedConversations[conversationId];
			delete state.conversationSockets[conversationId];
		}
		var ws = new WebSocket(config.wsUrl);
		state.conversationSockets[conversationId] = ws;
		debugLog('conversation connecting', conversationId);

		ws.onopen = function () {
			debugLog('conversation open', conversationId);
			clearConversationReconnect(conversationId);
			markConversationActivity(conversationId);

			var joinPayload = {
				type: 'join',
				conversationId: conversationId,
				role: config.role,
				sessionId: state.sessionId
			};

			if (isCustomerMode()) {
				joinPayload.phone = config.customer.phone || '';
			} else {
				if (conversation && conversation.phone) {
					joinPayload.phone = conversation.phone;
				}
				joinPayload.adminId = getAdminId();
				joinPayload.adminName = getAdminName();
				joinPayload.adminAvatarUrl = config.adminAvatarUrl || '';
			}

			ws.send(JSON.stringify(joinPayload));
		};

		ws.onmessage = function (event) {
			var msg;
			try { msg = JSON.parse(event.data); } catch (e) { return; }
			markConversationActivity(conversationId);
			handleRealtimeConversationMessage(conversationId, ws, msg);
		};

		ws.onclose = function () {
			if (state.conversationSockets[conversationId] === ws) {
				delete state.conversationSockets[conversationId];
				delete state.joinedConversations[conversationId];
				delete state.conversationActivityAt[conversationId];
			}
			if (!isCustomerMode()) {
				setLiveAdminParticipants(conversationId, []);
				if (isSameConversationId(state.selectedConversationId, conversationId)) updateHeader(getConversation(conversationId));
			}
			scheduleConversationReconnect(conversationId);
		};

		ws.onerror = function () { };
	}

	function closeInactiveConversationSockets(activeConversationId) {
		if (isCustomerMode()) return;
		Object.keys(state.conversationSockets).forEach(function (conversationId) {
			if (isSameConversationId(conversationId, activeConversationId)) return;
			var ws = state.conversationSockets[conversationId];
			delete state.conversationSockets[conversationId];
			delete state.joinedConversations[conversationId];
			delete state.conversationActivityAt[conversationId];
			clearConversationReconnect(conversationId);
			clearRealtimeReconnectState(conversationId);
			setLiveAdminParticipants(conversationId, []);
			if (ws) ws.close();
		});
	}

	function handleRealtimeConversationMessage(conversationId, ws, msg) {
		if (msg.type === 'ping') {
			ws.send(JSON.stringify({ type: 'pong' }));
			return;
		}

		if (msg.type === 'joined') {
			debugLog('joined', conversationId, msg);
			if (isConversationReconnecting(conversationId)) {
				setRealtimeReconnected(conversationId);
			} else {
				clearRealtimeReconnectState(conversationId);
			}
			state.joinedConversations[conversationId] = true;
			var joinedConversation = ensureConversation(conversationId, {
				peerOnline: !!msg.peerOnline,
				unreadCount: 0
			});
			if (!isCustomerMode()) {
				setLiveAdminParticipants(conversationId, msg.adminParticipants || []);
			}
			if (isSameConversationId(state.selectedConversationId, conversationId)) {
				updateHeader(joinedConversation);
			}
			renderList();
			return;
		}

		if (msg.type === 'history') {
			loadRealtimeHistory(conversationId, msg.messages || []);
			return;
		}

		if (msg.type === 'message') {
			var normalized = normalizeRealtimeMessage(conversationId, msg);
			if (normalized.id) markPendingMessageSent(normalized.id);
			if (msg.sessionId && msg.sessionId === state.sessionId && findDuplicateMessage(normalized)) {
				debugLog('echo suppressed', conversationId, msg.sessionId);
				return;
			}
			if (normalized.senderType === 'staff') {
				rememberAdminParticipant(conversationId, normalized);
				updateHeader(getConversation(conversationId));
			}
			appendMessage(normalized);
			return;
		}

		if (msg.type === 'sent') {
			markPendingMessageSent(msg.messageId || msg.id);
			return;
		}

		if (msg.type === 'system') {
			if (msg.code === 'peer_admin_joined') {
				rememberAdminParticipant(conversationId, msg);
				if (isSameConversationId(state.selectedConversationId, conversationId)) updateHeader(getConversation(conversationId));
				return;
			}
			if (msg.code === 'peer_admin_left') {
				removeAdminParticipant(conversationId, msg);
				if (isSameConversationId(state.selectedConversationId, conversationId)) updateHeader(getConversation(conversationId));
				return;
			}
			if (
				msg.code === 'customer_joined' ||
				msg.code === 'customer_disconnected' ||
				msg.text === 'Khách hàng đã tham gia cuộc trò chuyện' ||
				msg.text === 'Khách hàng đã ngắt kết nối'
			) {
				// If the conversation was merged into a newer session (id changed by ensureConversation),
				// getConversation returns null here. Clean up the stale socket and join the current
				// selected conversation (the merged target) rather than creating a phantom card.
				var presenceConversation = getConversation(conversationId);
				if (!presenceConversation) {
					_cleanupStaleSocketsExcept(state.selectedConversationId);
					if (state.selectedConversationId && !state.conversationSockets[state.selectedConversationId]) {
						joinRealtimeConversation(state.selectedConversationId);
					}
					return;
				}
				var isCustomerJoined = msg.code === 'customer_joined' || msg.text === 'Khách hàng đã tham gia cuộc trò chuyện';
				presenceConversation.peerOnline = isCustomerJoined;
				if (isSameConversationId(state.selectedConversationId, conversationId)) updateHeader(presenceConversation);
				renderList();

				// Auto-follow: when the selected conversation's customer disconnects,
				// find the same customer's active session (different browser) and switch.
				// Skipped in broadcast mode — messages already reach all sessions so no need to follow.
				if (!isCustomerJoined && !config.broadcastToAllSessions && isSameConversationId(state.selectedConversationId, conversationId)) {
					var disconnectedPhone = resolveConversationPhone(presenceConversation, conversationId);
					if (disconnectedPhone) {
						var samePhoneOthers = data.conversations.filter(function (c) {
							var cPhone = resolveConversationPhone(c);
							return c.id !== conversationId && cPhone && cPhone === disconnectedPhone;
						});
						// Pick the most recently active session among remaining browsers
						var fallback = sortConversationsByActivity(samePhoneOthers)[0] || null;
						if (fallback) {
							// Remove the phantom entry created for the now-merged old convId
							data.conversations = data.conversations.filter(function (c) { return c.id !== conversationId; });
							selectConversation(fallback.id);
						} else {
							// Not in local data yet — chain on the in-flight load (or start a new one).
							// Capture selected ID now; bail if admin manually navigated before callback resolves.
							var selectedAtDisconnect = state.selectedConversationId;
							loadInitialConversations().then(function () {
								if (state.selectedConversationId !== selectedAtDisconnect) return;
								var candidates = data.conversations.filter(function (c) {
									var cPhone = resolveConversationPhone(c);
									return c.id !== conversationId && cPhone && cPhone === disconnectedPhone;
								});
								var refreshed = sortConversationsByActivity(candidates)[0] || null;
								if (refreshed) {
									data.conversations = data.conversations.filter(function (c) { return c.id !== conversationId; });
									selectConversation(refreshed.id);
								}
							}).catch(function () {});
						}
					}
				}

				return;
			}
			if (msg.adminName || msg.adminId) {
				rememberAdminParticipant(conversationId, msg);
				updateHeader(getConversation(conversationId));
			}
			appendMessage({
				conversationId: conversationId,
				senderType: 'system',
				content: toPlainText(msg.text || ''),
				createdAt: formatCurrentTime()
			});
			return;
		}

		if (msg.type === 'typing') {
			showTypingIndicator(conversationId, msg);
			return;
		}

		if (msg.type === 'seen') {
			// Customer has seen messages up to a specific staff message.
			// Server sends { type:'seen', conversationId, lastReadMessageId, timestamp }.
			markStaffMessagesSeenUntilId(conversationId, msg.lastReadMessageId, msg.timestamp);
			return;
		}

		if (msg.type === 'delivered') {
			markStaffMessagesDeliveredUntilId(conversationId, msg.lastDeliveredMessageId, msg.timestamp);
			return;
		}
	}

	function normalizeRealtimeMessage(conversationId, msg) {
		msg = msg || {};
		var senderType = msg.senderType || roleToSenderType(msg.role);
		var rawCreatedAt = msg.createdAtRaw || msg.timestamp || msg.createdAt || '';
		var seenAtRaw = msg.seenAtRaw || msg.seenAt || '';
		var deliveredAtRaw = msg.deliveredAtRaw || msg.deliveredAt || '';
		return {
			id: msg.id || msg._id || ('m' + (msg.timestamp || Date.now())),
			conversationId: getCanonicalConversationId(msg.conversationId || conversationId, conversationId),
			senderType: senderType || 'system',
			adminId: msg.adminId || '',
			adminName: senderType === 'staff' ? (msg.adminName || msg.senderName || '') : '',
			adminAvatarUrl: msg.adminAvatarUrl || '',
			content: toPlainText(msg.text || msg.content || ''),
			imageUrl: msg.imageUrl || '',
			imageData: msg.imageData || '',
			imageName: msg.imageName || '',
			createdAt: formatTimestamp(rawCreatedAt) || formatCurrentTime(),
			createdAtRaw: rawCreatedAt,
			seenAt: formatTimestamp(seenAtRaw) || msg.seenAt || '',
			seenAtRaw: seenAtRaw,
			deliveredAt: formatTimestamp(deliveredAtRaw) || msg.deliveredAt || '',
			deliveredAtRaw: deliveredAtRaw,
			delivered: !!msg.delivered,
			// keep sessionId for potential future use
			sessionId: msg.sessionId || ''
		};
	}

	function loadRealtimeHistory(conversationId, messages) {
		var beforeKey = getThreadRenderKey(conversationId, getMessages(conversationId));
		var normalizedMessages = (messages || []).map(function (message) {
			return normalizeRealtimeMessage(conversationId, message);
		});
		normalizedMessages.forEach(function (message) {
			upsertMessage(message);
		});
		applyPendingReceipts(conversationId, { render: false });
		var afterKey = getThreadRenderKey(conversationId, getMessages(conversationId));
		var changed = beforeKey !== afterKey;
		if (changed) saveCachedConversationMessages(conversationId);

		var latest = getLatestNonSystemMessage(conversationId);
		if (latest) {
			updateLastMessage(conversationId, latest.content || (latest.imageUrl ? 'Đã gửi ảnh đính kèm.' : ''), true, latest.createdAt || formatCurrentTime());
			updateConversationLastMessageMeta(conversationId, latest);
		}

		if (changed && isSameConversationId(state.selectedConversationId, conversationId)) {
			renderThread({ focus: true });
		} else if (changed) {
			renderList();
		}
	}

	function showTypingIndicator(conversationId, typing) {
		state.typingByConversation[conversationId] = typing || { role: 'customer' };
		renderList();
		var messages = qs('.ec-cw__messages');
		if (!messages || !state.selectedConversationId || !isSameConversationId(conversationId, state.selectedConversationId)) return;
		var existing = qs('.ec-cw__typing', messages);
		var isCustomerTyping = !typing || typing.role === 'customer';
		var conversation = getConversation(conversationId);
		var adminKey = isCustomerTyping ? '' : getAdminParticipantKey(typing);
		var adminParticipant = !isCustomerTyping && state.adminParticipantsByConversation[conversationId]
			? state.adminParticipantsByConversation[conversationId][adminKey]
			: null;
		var adminLabel = (adminParticipant && adminParticipant.label) || (typing && (typing.adminName || typing.senderName || typing.name)) || 'Tư vấn viên';
		var adminAvatarUrl = (adminParticipant && adminParticipant.avatarUrl) || (typing && (typing.adminAvatarUrl || typing.avatarUrl)) || '';
		var adminColorKey = (adminParticipant && adminParticipant.colorKey) || getAdminAvatarColorKey(adminKey || adminLabel);
		var colors = isCustomerTyping
			? getAvatarColor(getCustomerAvatarColorKey(conversation))
			: getAvatarColor(adminColorKey);
		var avatarText = isCustomerTyping ? getCustomerAvatarText() : ((adminParticipant && adminParticipant.initials) || getAdminInitials(adminLabel, 'NV'));
		var label = isCustomerTyping ? 'Khách hàng đang nhập' : ((typing.adminName || 'Tư vấn viên') + ' đang nhập');
		var isStaffTypingOnRight = !isCustomerTyping && !isCustomerMode();
		var typingClass = 'ec-cw__typing' + (isStaffTypingOnRight ? ' ec-cw__typing--staff' : '');
		var avatarHtml = adminAvatarUrl && !isCustomerTyping
			? '<img src="' + escapeHtml(adminAvatarUrl) + '" alt="' + escapeHtml(adminLabel) + '">'
			: escapeHtml(avatarText);
		var html = [
			'<div class="ec-cw__typing-avatar" style="background:' + colors.bg + ';color:' + colors.text + '" title="' + escapeHtml(label) + '">' + avatarHtml + '</div>',
			'<div class="ec-cw__typing-bubble" title="' + escapeHtml(label) + '">',
			'<span class="ec-cw__typing-dot"></span>',
			'<span class="ec-cw__typing-dot"></span>',
			'<span class="ec-cw__typing-dot"></span>',
			'</div>'
		].join('');
		if (!existing) {
			messages.insertAdjacentHTML('beforeend', '<div class="' + typingClass + '"></div>');
			existing = qs('.ec-cw__typing', messages);
		}
		existing.className = typingClass;
		existing.innerHTML = html;
		settleThreadView({ smooth: true });
		clearTimeout(state.typingTimersByConversation[conversationId]);
		state.typingTimersByConversation[conversationId] = setTimeout(function () {
			var indicator = qs('.ec-cw__typing', messages);
			if (indicator) indicator.remove();
			delete state.typingByConversation[conversationId];
			delete state.typingTimersByConversation[conversationId];
			renderList();
		}, 3000);
	}

	function clearTypingIndicator() {
		clearTimeout(state.typingTimer);
		var messages = qs('.ec-cw__messages');
		var indicator = messages && qs('.ec-cw__typing', messages);
		if (indicator) indicator.remove();
		if (state.selectedConversationId) {
			clearTimeout(state.typingTimersByConversation[state.selectedConversationId]);
			delete state.typingByConversation[state.selectedConversationId];
			delete state.typingTimersByConversation[state.selectedConversationId];
			renderList();
		}
	}

	// ── sendRealtimeTyping: admin typing → sent to conversation socket ──────────
	// Server will relay to: customer (no adminName) + other admin tabs (with adminName).
	// Debounce 2 s to avoid flooding.
	function sendRealtimeTyping() {
		if (!isRealtimeEnabled() || !state.selectedConversationId) return;
		if (state.typingDebounce) return;
		state.typingDebounce = setTimeout(function () {
			state.typingDebounce = null;
		}, 2000);
		var ws = state.conversationSockets[state.selectedConversationId];
		if (!ws || ws.readyState !== WebSocket.OPEN) return;
		ws.send(JSON.stringify({
			type: 'typing',
			conversationId: state.selectedConversationId,
			adminId: isCustomerMode() ? '' : getAdminId(),
			adminName: isCustomerMode() ? '' : getAdminName(),
			adminAvatarUrl: isCustomerMode() ? '' : (config.adminAvatarUrl || '')
		}));
	}

		function canCustomerSendSeen(conversationId) {
			return !!(
				isCustomerMode() &&
				isRealtimeEnabled() &&
				state.isOpen &&
				conversationId &&
				isSameConversationId(state.selectedConversationId, conversationId) &&
				document.visibilityState === 'visible' &&
				document.hasFocus() &&
				state.customerWindowFocused
			);
	}

	function getLatestStaffMessage(conversationId) {
		var messages = getMessages(conversationId);
		for (var i = messages.length - 1; i >= 0; i--) {
			var message = messages[i];
			if (message.senderType === 'staff') {
				return message;
			}
		}
		return null;
	}

	function trySendRealtimeSeen(conversationId) {
		if (!canCustomerSendSeen(conversationId)) return;

		var latestStaffMessage = getLatestStaffMessage(conversationId);
		if (!latestStaffMessage || !latestStaffMessage.id) return;

		sendRealtimeSeen(conversationId, latestStaffMessage.id);
	}

	function markCustomerSeenMessage(conversationId, messageId) {
		if (!messageId || !canCustomerSendSeen(conversationId) || state.lastCustomerSeenMessageId === messageId) return;
		state.lastCustomerSeenMessageId = messageId;
		sendRealtimeSeen(conversationId, messageId);
	}

	function watchCustomerSeenMessage(row) {
		if (!isCustomerMode() || !row || !row.getAttribute('data-message-id')) return;
		if (!state.customerSeenObserver && window.IntersectionObserver) {
			state.customerSeenObserver = new IntersectionObserver(function (entries) {
				entries.forEach(function (entry) {
					if (!entry.isIntersecting || entry.intersectionRatio < 0.6) return;
					markCustomerSeenMessage(state.selectedConversationId, entry.target.getAttribute('data-message-id'));
				});
			}, { root: qs('.ec-cw__messages'), threshold: [0.6] });
		}
		if (state.customerSeenObserver) state.customerSeenObserver.observe(row);
	}

	function checkVisibleCustomerSeenMessages() {
		if (!canCustomerSendSeen(state.selectedConversationId)) return;
		var messages = qs('.ec-cw__messages');
		if (!messages) return;
		var containerRect = messages.getBoundingClientRect();
		qsa('.ec-cw__msg[data-message-id]', messages).forEach(function (row) {
			watchCustomerSeenMessage(row);
			var rect = row.getBoundingClientRect();
			var visibleHeight = Math.min(rect.bottom, containerRect.bottom) - Math.max(rect.top, containerRect.top);
			var ratio = rect.height > 0 ? visibleHeight / rect.height : 0;
			if (ratio >= 0.6) markCustomerSeenMessage(state.selectedConversationId, row.getAttribute('data-message-id'));
		});
	}

	// sendRealtimeSeen is only used by customer side. Admin receives the receipt via WebSocket.
	function sendRealtimeSeen(conversationId, lastReadMessageId) {
		if (!isCustomerMode() || !isRealtimeEnabled() || !conversationId || !lastReadMessageId) return;
		var ws = state.conversationSockets[conversationId];
		if (!ws || ws.readyState !== WebSocket.OPEN) return;
		ws.send(JSON.stringify({
			type: 'seen',
			conversationId: conversationId,
			lastReadMessageId: lastReadMessageId
		}));
	}

	function readImageFile(file) {
		if (!canUploadImages()) return;
		if (!file || !/^image\//.test(file.type)) {
			alert('Vui lòng chọn đúng file ảnh.');
			return;
		}
		var reader = new FileReader();
		reader.onload = function (event) {
			state.selectedImage = { name: file.name, data: event.target.result };
			renderImagePreview();
		};
		reader.readAsDataURL(file);
	}

	function applyConfig(custom) {
		closeRealtimeConnections();
		config = normalizeConfig(Object.assign({}, rawConfig, custom || {}));
		data = mergeData(defaultData, config);
		state.selectedConversationId = isCustomerMode() ? config.conversationId : null;
		state.drafts = {};
		state.selectedImage = null;
		state.hasLoadedInitialConversations = false;
		state.loadingMessagesByConversation = {};
		state.loadingOlderMessagesByConversation = {};
		state.loadedMessagesByConversation = {};
		state.messageNextCursorByConversation = {};
		state.exhaustedOlderMessagesByConversation = {};
		state.olderMessageLoadRequestedAt = {};
		state.customerReplyEngaged = false;
		state.lastCustomerSeenMessageId = null;
		if (state.customerSeenObserver) state.customerSeenObserver.disconnect();
		state.customerSeenObserver = null;

		var root = document.getElementById(WIDGET_ID);
		if (root) {
			root.classList.remove('ec-cw--admin', 'ec-cw--customer', 'ec-cw--no-upload', 'has-thread', 'is-thread');
			root.classList.add('ec-cw--' + config.role);
			if (!canUploadImages()) root.classList.add('ec-cw--no-upload');
			root.style.setProperty('--chat-primary', config.primaryColor);
			var title = qs('.ec-cw__header-title', root);
			var subtitle = qs('.ec-cw__header-subtitle', root);
			if (title) title.textContent = config.title;
			if (subtitle) subtitle.textContent = config.status || config.subtitle;
			updateHeader(getConversation(state.selectedConversationId));
		}
	}

	function closeRealtimeConnections() {
		if (state.subWs) {
			state.subWs.onclose = null;
			state.subWs.close();
			state.subWs = null;
		}
		clearTimeout(state.subReconnectTimer);
		state.subReconnectAttempt = 0;
		state.lastSubscriberActivityAt = 0;
		Object.keys(state.conversationReconnectTimers).forEach(function (conversationId) {
			clearConversationReconnect(conversationId);
		});
		Object.keys(state.conversationSockets).forEach(function (conversationId) {
			var ws = state.conversationSockets[conversationId];
			if (ws) {
				ws.onclose = null;
				ws.close();
			}
		});
		state.conversationSockets = {};
		state.joinedConversations = {};
		state.conversationActivityAt = {};
		Object.keys(state.realtimeReconnectHideTimers).forEach(function (key) {
			clearTimeout(state.realtimeReconnectHideTimers[key]);
		});
		state.realtimeReconnectingByKey = {};
		state.realtimeReconnectedByKey = {};
		state.realtimeReconnectHideTimers = {};
		updateReconnectUi();
	}

	// Returns the list of conversation IDs that an outbound admin message should be sent to.
	// Default: only the currently selected conversation.
	// Future broadcast mode: set config.broadcastToAllSessions = true to fan out to every
	// active session sharing the same phone number (all open customer browsers).
	function getTargetConversationIds() {
		if (!state.selectedConversationId) return [];
		if (!config.broadcastToAllSessions) return [state.selectedConversationId];
		var selectedConv = getConversation(state.selectedConversationId);
		var phone = resolveConversationPhone(selectedConv, state.selectedConversationId);
		if (!phone) return [state.selectedConversationId];
		var ids = data.conversations
			.filter(function (c) {
				var cPhone = resolveConversationPhone(c);
				return cPhone && cPhone === phone;
			})
			.map(function (c) { return c.id; });
		return ids.length ? ids : [state.selectedConversationId];
	}

	function addActiveMessage() {
		var replyText = qs('.ec-cw__reply-text');
		var upload = qs('.ec-cw__image-upload');
		var content = replyText ? toPlainText(replyText.value).trim() : '';
		if (state.sendLocked) return;
		if (!canUploadImages()) state.selectedImage = null;
		if ((!content && !state.selectedImage) || !state.selectedConversationId) return;
		if (isRealtimeEnabled() && state.selectedImage) {
			alert('Gửi ảnh realtime cần cấu hình upload R2 để lấy imageUrl HTTPS.');
			return;
		}

		var finalContent = content || 'Đã gửi ảnh đính kèm.';
		state.sendLocked = true;
		setTimeout(function () {
			state.sendLocked = false;
		}, 300);

		var message = {
			id: createClientMessageId(),
			conversationId: state.selectedConversationId,
			senderType: isCustomerMode() ? 'customer' : 'staff',
			adminId: isCustomerMode() ? '' : getAdminId(),
			adminName: isCustomerMode() ? '' : getAdminName(),
			adminAvatarUrl: isCustomerMode() ? '' : (config.adminAvatarUrl || ''),
			sessionId: state.sessionId,
			content: finalContent,
			imageData: state.selectedImage ? state.selectedImage.data : '',
			imageName: state.selectedImage ? state.selectedImage.name : '',
			createdAt: formatCurrentTime(),
			createdAtRaw: Date.now(),
			deliveryState: isRealtimeEnabled() ? 'sending' : 'sent'
		};

		if (isRealtimeEnabled()) {
			try {
				if (!sendRealtimeMessagePayload(message)) {
					message.deliveryState = 'failed';
				}
			} catch (error) {
				debugLog('send message failed', error && error.message ? error.message : error);
				message.deliveryState = 'failed';
			}
		}

		upsertMessage(message);
		saveCachedConversationMessages(message.conversationId);
		if (message.deliveryState === 'sending') startPendingMessageTimer(message);
		if (message.senderType === 'staff') {
			rememberAdminParticipant(message.conversationId, message);
			updateHeader(getConversation(message.conversationId));
		}
		updateLastMessage(state.selectedConversationId, state.selectedImage ? finalContent + ' ' + state.selectedImage.name : finalContent, true, message.createdAt);
		updateConversationLastMessageMeta(state.selectedConversationId, message);
		state.drafts[state.selectedConversationId] = '';
		state.selectedImage = null;
		if (replyText) {
			replyText.value = '';
			resizeReplyText(replyText);
		}
		if (upload) upload.value = '';
		renderImagePreview();
		updateSendButtonState();
		appendMessageToThread(message, { smooth: true, focus: true });
		renderList();
	}

	function appendMessage(message) {
		if (!message || !message.conversationId) return;
		message.id = message.id || ('m' + Date.now());
		message.senderType = message.senderType || 'customer';
		message.content = toPlainText(message.content || message.text || '');
		message.createdAt = message.createdAt || formatCurrentTime();
		if (isPresenceSystemMessage(message)) return;
		clearTimeout(state.typingTimersByConversation[message.conversationId]);
		delete state.typingByConversation[message.conversationId];
		delete state.typingTimersByConversation[message.conversationId];
		if (message.senderType === 'staff') {
			rememberAdminParticipant(message.conversationId, message);
		}
		var duplicate = findDuplicateMessage(message);
		var storedMessage = upsertMessage(message);
		applyPendingReceipts(message.conversationId, { render: false });
		saveCachedConversationMessages(message.conversationId);
		var isSystemMessage = message.senderType === 'system';
		if (isSameConversationId(state.selectedConversationId, message.conversationId)) {
			if (!isSystemMessage) clearTypingIndicator();
			updateLastMessage(message.conversationId, message.content || (message.imageUrl ? 'Đã gửi ảnh đính kèm.' : ''), true, message.createdAt);
			updateConversationLastMessageMeta(message.conversationId, message);
			if (!isCustomerMode() && message.senderType === 'customer') {
				markConversationRead(message.conversationId);
			}
			if (duplicate) {
				renderThread();
			} else {
				appendMessageToThread(storedMessage, { smooth: true });
			}
			updateHeader(getConversation(message.conversationId));
			renderList();
			if (isCustomerMode() && message.senderType === 'staff') {
				setTimeout(checkVisibleCustomerSeenMessages, 120);
			}
		} else {
			var conversation = ensureConversation(message.conversationId);
			if (!isSystemMessage) {
				updateLastMessage(message.conversationId, message.content || (message.imageUrl ? 'Đã gửi ảnh đính kèm.' : ''), false, message.createdAt);
				updateConversationLastMessageMeta(message.conversationId, message);
				conversation.unreadCount = Number(conversation.unreadCount || 0) + 1;
				moveConversationToTop(message.conversationId);
			}
			renderList();
		}
	}

	function selectConversation(conversationId) {
		if (!getConversation(conversationId)) return;
		clearTypingIndicator();
		closeInactiveConversationSockets(conversationId);
		state.selectedConversationId = conversationId;
		state.selectedImage = null;
		state.expandedReceiptId = null;
		state.customerReplyEngaged = false;
		state.lastCustomerSeenMessageId = null;
		if (state.customerSeenObserver) state.customerSeenObserver.disconnect();
		state.customerSeenObserver = null;
		loadCachedConversationMessages(conversationId);
		renderImagePreview();
		joinRealtimeConversation(conversationId);
		renderThread({ focus: true });
		markConversationRead(conversationId);
		loadConversationMessages(conversationId);
	}

	function maybeLoadOlderMessagesFromScroll(messagesEl) {
		if (isCustomerMode() || !messagesEl) return;
		var conversationId = messagesEl.getAttribute('data-conversation-id') || state.selectedConversationId;
		if (!conversationId || !isSameConversationId(conversationId, state.selectedConversationId)) return;
		if (messagesEl.scrollTop > 80) return;
		var now = Date.now();
		if (now - (state.olderMessageLoadRequestedAt[conversationId] || 0) < 400) return;
		state.olderMessageLoadRequestedAt[conversationId] = now;
		loadOlderConversationMessages(state.selectedConversationId);
	}

	function bindEvents() {
		var root = document.getElementById(WIDGET_ID);
		if (!root || root.getAttribute('data-bound') === '1') return;
		root.setAttribute('data-bound', '1');

		qs('.ec-cw__toggle', root).addEventListener('click', function () {
			state.isOpen = true;
			root.classList.add('is-open');
			if (isCustomerMode()) {
				state.selectedConversationId = state.selectedConversationId || config.conversationId;
				renderThread({ focus: true });
				setTimeout(checkVisibleCustomerSeenMessages, 350);
			} else if (state.selectedConversationId) {
				renderThread({ focus: true });
			} else {
				root.classList.remove('has-thread', 'is-thread');
				renderList();
			}
		});

		qsa('.ec-cw__minimize,.ec-cw__close', root).forEach(function (button) {
			button.addEventListener('click', function () {
				state.isOpen = false;
				root.classList.remove('is-open', 'is-thread', 'has-thread');
				state.selectedImage = null;
				state.customerReplyEngaged = false;
				renderImagePreview();
			});
		});

		qs('.ec-cw__list', root).addEventListener('click', function (event) {
			if (event.target.closest('.ec-cw__list-close')) {
				state.isOpen = false;
				root.classList.remove('is-open', 'is-thread', 'has-thread');
				state.selectedImage = null;
				state.customerReplyEngaged = false;
				renderImagePreview();
				return;
			}
			var item = event.target.closest('.ec-cw__conv');
			if (!item) return;
			state.isAdminParticipantsOpen = false;
			selectConversation(item.getAttribute('data-conversation-id'));
		});

		qs('.ec-cw__list', root).addEventListener('input', function (event) {
			if (!event.target.closest('.ec-cw__list-search-input')) return;
			var value = event.target.value;
			var cursor = event.target.selectionStart;
			state.conversationSearch = value;
			renderList();
			var input = qs('.ec-cw__list-search-input', root);
			if (input) {
				input.focus();
				if (typeof cursor === 'number' && typeof input.setSelectionRange === 'function') {
					input.setSelectionRange(cursor, cursor);
				}
			}
		});

		qsa('.ec-cw__back,.ec-cw__header-back', root).forEach(function (button) {
			button.addEventListener('click', function () {
			state.isAdminParticipantsOpen = false;
			root.classList.remove('is-thread');
			state.selectedImage = null;
			renderImagePreview();
			renderList();
			});
		});

		root.addEventListener('click', function (event) {
			var retryReceipt = event.target.closest('.ec-cw__receipt[data-retry="1"]');
			if (retryReceipt) {
				event.preventDefault();
				retryFailedMessage(retryReceipt.getAttribute('data-msg-id'));
				return;
			}
			var avatars = event.target.closest('.ec-cw__header-admin-avatars');
			var popover = event.target.closest('.ec-cw__admin-participants-popover');

			if (avatars) {
				event.stopPropagation();
				var conversation = getConversation(state.selectedConversationId);
				if (!conversation || !getHeaderAdminParticipants(conversation).length) return;
				state.isAdminParticipantsOpen = !state.isAdminParticipantsOpen;
				updateHeader(conversation);
				return;
			}
			if (!popover && state.isAdminParticipantsOpen) {
				state.isAdminParticipantsOpen = false;
				updateHeader(getConversation(state.selectedConversationId));
			}
		});

		root.addEventListener('keydown', function (event) {
			if (event.key !== 'Enter' && event.key !== ' ') return;
			var retryReceipt = event.target.closest('.ec-cw__receipt[data-retry="1"]');
			if (!retryReceipt) return;
			event.preventDefault();
			retryFailedMessage(retryReceipt.getAttribute('data-msg-id'));
		});

		qs('.ec-cw__send', root).addEventListener('click', addActiveMessage);

		qs('.ec-cw__reply-text', root).addEventListener('input', function () {
			if (state.selectedConversationId) state.drafts[state.selectedConversationId] = this.value;
			resizeReplyText(this);
			updateSendButtonState();
			sendRealtimeTyping();
		});

		qs('.ec-cw__reply-text', root).addEventListener('mousedown', function () {
			if (!isCustomerMode()) return;
			setTimeout(function () {
				checkVisibleCustomerSeenMessages();
			}, 0);
		});

		qs('.ec-cw__reply-text', root).addEventListener('touchstart', function () {
			if (!isCustomerMode()) return;
			setTimeout(function () {
				checkVisibleCustomerSeenMessages();
			}, 0);
		});

		qs('.ec-cw__reply-text', root).addEventListener('blur', function () {
			if (isCustomerMode()) checkVisibleCustomerSeenMessages();
		});

		qs('.ec-cw__reply-text', root).addEventListener('compositionstart', function () {
			state.isComposing = true;
		});

		qs('.ec-cw__reply-text', root).addEventListener('compositionend', function () {
			state.isComposing = false;
		});

		qs('.ec-cw__reply-text', root).addEventListener('keydown', function (event) {
			if (event.key === 'Enter' && !event.shiftKey) {
				if (event.isComposing || state.isComposing || event.keyCode === 229) return;
				event.preventDefault();
				addActiveMessage();
			}
		});

		qs('.ec-cw__image-upload', root).addEventListener('change', function () {
			readImageFile(this.files && this.files[0]);
		});

		qs('.ec-cw__img-preview', root).addEventListener('click', function (event) {
			if (!event.target.closest('.ec-cw__remove-image')) return;
			state.selectedImage = null;
			qs('.ec-cw__image-upload', root).value = '';
			renderImagePreview();
			updateSendButtonState();
		});

		qs('.ec-cw__messages', root).addEventListener('scroll', function () {
			maybeLoadOlderMessagesFromScroll(this);
			if (isCustomerMode()) checkVisibleCustomerSeenMessages();
		});

		document.addEventListener('visibilitychange', function () {
			if (document.visibilityState === 'hidden' && isCustomerMode()) {
				state.customerReplyEngaged = false;
				state.customerWindowFocused = false;
				var replyText = qs('.ec-cw__reply-text', root);
				if (replyText) replyText.blur();
			} else if (document.visibilityState === 'visible' && isCustomerMode()) {
				state.customerWindowFocused = document.hasFocus();
				checkVisibleCustomerSeenMessages();
			}
			if (document.visibilityState === 'visible') {
				recoverRealtime('visible');
			}
		});

		window.addEventListener('focus', function () {
			recoverRealtime('focus');
			if (isCustomerMode()) {
				state.customerWindowFocused = true;
				checkVisibleCustomerSeenMessages();
			}
		});

		window.addEventListener('online', function () {
			recoverRealtime('online');
		});

		window.addEventListener('blur', function () {
			if (!isCustomerMode()) return;
			state.customerReplyEngaged = false;
			state.customerWindowFocused = false;
			var replyText = qs('.ec-cw__reply-text', root);
			if (replyText) replyText.blur();
		});
	}

	function init(custom) {
		if (custom) {
			rawConfig = Object.assign({}, rawConfig, custom);
			applyConfig(custom);
		} else if (isCustomerMode() && !state.selectedConversationId) {
			state.selectedConversationId = config.conversationId;
		}
		injectStyles();
		buildMarkup();
		bindEvents();
		if (isCustomerMode()) {
			renderThread();
		} else if (state.selectedConversationId) {
			renderThread();
		} else {
			renderList();
		}
		if (!isCustomerMode()) loadInitialConversations();
		if (config.autoConnect) {
			connectSubscriber();
			if (state.selectedConversationId) joinRealtimeConversation(state.selectedConversationId);
			startRealtimeWatchdog();
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}

	window.ECChatbotWidget = {
		init: init,
		configure: function (custom) {
			rawConfig = Object.assign({}, rawConfig, custom || {});
			applyConfig(custom);
			if (isCustomerMode()) {
				renderThread();
			} else if (state.selectedConversationId) {
				renderThread();
			} else {
				renderList();
			}
			if (!isCustomerMode()) loadInitialConversations();
			if (config.autoConnect) {
				connectSubscriber();
				if (state.selectedConversationId) joinRealtimeConversation(state.selectedConversationId);
				startRealtimeWatchdog();
			}
		},
		appendMessage: appendMessage,
		selectConversation: selectConversation,
		joinConversation: function (conversationId) {
			ensureConversation(conversationId);
			selectConversation(conversationId);
		},
		getData: function () { return data; }
	};
})();

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SIAssist - Smart Assistant</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Markdown parser -->
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Inter", sans-serif;
        }

        body {
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 50%, #0369a1 100%);
            min-height: 100vh;
            display: flex;
            align-items: flex-start;
            justify-content: flex-end;
            padding: 30px;
        }

        .layout {
            display: flex;
            height: 90vh;
            max-height: 850px;
            width: 100%;
            max-width: 440px;
            background: white;
            border-radius: 30px;
            overflow: hidden;
            box-shadow: 0 25px 70px rgba(0, 0, 0, 0.5);
            border: 10px solid #1e293b;
            position: relative;
        }

        .search-bar {
            padding: 10px 14px;
            border-radius: 10px;
            border: 2px solid rgba(14, 165, 233, 0.25);
            outline: none;
            width: 100%;
            margin-bottom: 12px;
            font-size: 13px;
            transition: .3s;
        }

        .search-bar:focus {
            border-color: #0ea5e9;
        }

        /* SIDEBAR */
        .sidebar {
            width: 280px;
            min-width: 280px;
            margin-left: -280px;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            padding: 20px;
            display: flex;
            flex-direction: column;
            transition: margin-left .3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: none;
            border-right: none;
            overflow-y: auto;
            flex-shrink: 0;
            position: absolute;
            z-index: 100;
            height: 100%;
        }


        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(14, 165, 233, 0.3);
            border-radius: 10px;
        }

        .sidebar.show {
            margin-left: 0;
            width: 80%;
            box-shadow: 4px 0 24px rgba(0, 0, 0, 0.1);
            border-right: 1px solid rgba(14, 165, 233, 0.1);
        }

        /* Input modal untuk rename */
        .rename-input {
            width: 100%;
            padding: 12px 14px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14px;
            margin-bottom: 20px;
            outline: none;
            transition: border-color 0.2s;
        }

        .rename-input:focus {
            border-color: #0ea5e9;
        }

        .confirm-modal-btn.primary {
            background: linear-gradient(135deg, #0ea5e9, #0284c7);
            color: #fff;
        }

        .confirm-modal-btn.primary:hover {
            transform: translateY(-2px);
        }


        .main-chat {
            width: 100%;
            min-width: 100%;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            position: relative;
            transition: all .3s ease;
        }


        .main-chat::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0);
            backdrop-filter: blur(0px);
            pointer-events: none;
            transition: all .3s ease;
            z-index: 5;
        }

        .layout.sidebar-open .main-chat::after {
            background: rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(2px);
            pointer-events: auto;
        }
         .sidebar-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e0f2fe;
        }

        .sidebar-logo {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            box-shadow: 0 4px 12px rgba(14, 165, 233, 0.4);
        }

        .sidebar-title {
            font-size: 19px;
            font-weight: 700;
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .btn-sidebar {
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            color: white;
            border: none;
            padding: 12px 16px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all .3s ease;
            box-shadow: 0 4px 12px rgba(14, 165, 233, 0.4);
        }

        .btn-sidebar:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(14, 165, 233, 0.5);
        }

        .btn-sidebar:active {
            transform: translateY(0);
        }

        .section-title {
            font-size: 11px;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 15px 0 10px 0;
        }

        .sidebar ul {
            list-style: none;
        }

        .sidebar ul li {
            padding: 10px 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            border-radius: 8px;
            transition: all .2s ease;
            color: #334155;
            font-size: 13px;
            margin-bottom: 3px;
            position: relative;
        }

        .sidebar ul li:hover {
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.1) 0%, rgba(2, 132, 199, 0.1) 100%);
            transform: translateX(5px);
            color: #0284c7;
        }

        .chat-item-text {
            flex: 1;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* 3-dot Menu Button */
        .chat-menu-btn {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            border: none;
            background: transparent;
            cursor: default;
            font-size: 16px;
            display: none;
            align-items: center;
            justify-content: center;
            transition: all .2s ease;
            color: #64748b;
            flex-shrink: 0;
        }

        .sidebar ul li:hover .chat-menu-btn {
            display: flex;
        }

        .chat-menu-btn:hover {
            background: rgba(14, 165, 233, 0.2);
            color: #0284c7;
        }

        /* Dropdown Menu */
        .chat-dropdown {
            position: absolute;
            right: 0;
            top: 100%;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            min-width: 140px;
            z-index: 100;
            display: none;
            overflow: hidden;
            border: 1px solid rgba(14, 165, 233, 0.1);
        }

        .chat-dropdown.show {
            display: block;
            animation: fadeIn 0.2s ease;
        }

        .chat-dropdown-item {
            padding: 10px 14px;
            font-size: 13px;
            color: #334155;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all .15s ease;
        }

        .chat-dropdown-item:hover {
            background: rgba(14, 165, 233, 0.1);
            color: #0284c7;
        }

        .chat-dropdown-item.danger {
            color: #ef4444;
        }

        .chat-dropdown-item.danger:hover {
            background: rgba(239, 68, 68, 0.1);
            color: #dc2626;
        }

        .chat-dropdown-item .menu-icon {
            font-size: 14px;
        }

        /* Pinned Chat Style */
        .sidebar ul li.pinned {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(217, 119, 6, 0.08) 100%);
            border-left: 3px solid #f59e0b;
        }

        /* Active (Currently Selected) Chat Style */
        .sidebar ul li.active {
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.2) 0%, rgba(2, 132, 199, 0.15) 100%);
            border-left: 4px solid #0284c7;
            font-weight: 600;
            color: #0369a1 !important;
        }

        .sidebar ul li.active::before {
            content: '▶';
            position: absolute;
            right: 10px;
            color: #0ea5e9;
            font-size: 10px;
        }

        /* Both pinned AND active - Blue takes priority */
        .sidebar ul li.pinned.active {
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.2) 0%, rgba(2, 132, 199, 0.15) 100%);
            border-left: 4px solid #0284c7;
        }

        .pin-indicator {
            font-size: 10px;
            margin-left: 4px;
        }

        /* When dropdown is open, disable hover on other items */
        .chat-list.menu-active li {
            pointer-events: none;
        }

        .chat-list.menu-active li:hover {
            background: transparent;
            transform: none;
            color: #334155;
        }

        .chat-list.menu-active li:hover .chat-menu-btn {
            display: none;
        }

        /* Keep active item interactive */
        .chat-list.menu-active li.menu-open {
            pointer-events: auto;
        }

        .chat-list.menu-active li.menu-open .chat-menu-btn {
            display: flex;
        }

        .settings-section {
            margin-top: auto;
            padding-top: 15px;
            border-top: 2px solid #e0f2fe;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            animation: fadeIn 0.3s ease;
        }

        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            border-radius: 20px;
            padding: 30px;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: slideUp 0.3s ease;
        }


        body {
            background: #f5f7fa;
            color: #222;
            transition: 0.3s ease;
        }


        body.dark-theme {
            background: #121212 !important;
            color: #e6e6e6 !important;
        }

        /* Layout & Containers */
        body.dark-theme .layout {
            background: #1a1a2e !important;
            border-color: #2d2d44 !important;
        }

        body.dark-theme .sidebar,
        body.dark-theme .chat-container,
        body.dark-theme .modal-content {
            background: #1e1e2e !important;
            color: #e6e6e6 !important;
        }

        /* Chat Box */
        body.dark-theme .chat-box {
            background: #16161a !important;
        }

        /* Messages */
        body.dark-theme .bot-message {
            background: #2a2a3e !important;
            color: #e6e6e6 !important;
            border-color: #3a3a5e !important;
        }

        /* Input Area */
        body.dark-theme .input-area {
            background: #1e1e2e !important;
            border-color: #3a3a5e !important;
        }

        body.dark-theme .chat-input {
            background: #2a2a3e !important;
            color: #fff !important;
            border-color: #3a3a5e !important;
        }

        body.dark-theme .chat-input:focus {
            border-color: #0ea5e9 !important;
        }

        /* Inputs, Selects, Textareas */
        body.dark-theme input,
        body.dark-theme select,
        body.dark-theme textarea {
            background: #2a2a3e !important;
            color: #fff !important;
            border-color: #3a3a5e !important;
        }

        /* Chat Item Text */
        body.dark-theme .chat-item-text {
            color: #f0f0f0 !important;
        }

        /* Sidebar List Items */
        body.dark-theme .sidebar ul li {
            color: #c0c0c0 !important;
        }

        body.dark-theme .sidebar ul li:hover {
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.15) 0%, rgba(2, 132, 199, 0.15) 100%) !important;
            color: #7dd3fc !important;
        }

        body.dark-theme .sidebar ul li.pinned {
            background: linear-gradient(135deg, rgba(251, 191, 36, 0.15) 0%, rgba(245, 158, 11, 0.1) 100%) !important;
            border-left-color: #fbbf24 !important;
        }

        body.dark-theme .sidebar ul li.active {
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.25) 0%, rgba(2, 132, 199, 0.2) 100%) !important;
            color: #7dd3fc !important;
        }

        body.dark-theme .sidebar ul li.active::before {
            color: #38bdf8 !important;
        }

        body.dark-theme .sidebar ul li.pinned.active {
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.25) 0%, rgba(2, 132, 199, 0.2) 100%) !important;
        }

        /* Dropdown Menu */
        body.dark-theme .chat-dropdown {
            background: #252538 !important;
            border-color: #3a3a5e !important;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4) !important;
        }

        body.dark-theme .chat-dropdown-item {
            color: #e0e0e0 !important;
        }

        body.dark-theme .chat-dropdown-item:hover {
            background: rgba(14, 165, 233, 0.2) !important;
            color: #7dd3fc !important;
        }

        body.dark-theme .chat-dropdown-item.danger {
            color: #f87171 !important;
        }

        body.dark-theme .chat-dropdown-item.danger:hover {
            background: rgba(239, 68, 68, 0.2) !important;
        }

        /* Chat Menu Button */
        body.dark-theme .chat-menu-btn {
            color: #9ca3af !important;
        }

        body.dark-theme .chat-menu-btn:hover {
            background: rgba(14, 165, 233, 0.3) !important;
            color: #7dd3fc !important;
        }

        /* Confirm Modal Dark Mode */
        body.dark-theme .confirm-modal {
            background: #1e1e2e !important;
            color: #e6e6e6 !important;
        }

        body.dark-theme .confirm-modal-title {
            color: #e6e6e6 !important;
        }

        body.dark-theme .confirm-modal-message {
            color: #9ca3af !important;
        }

        body.dark-theme .confirm-modal-btn.cancel {
            background: #2a2a3e !important;
            color: #e0e0e0 !important;
        }

        body.dark-theme .confirm-modal-btn.cancel:hover {
            background: #3a3a5e !important;
        }

        body.dark-theme .rename-input {
            background: #2a2a3e !important;
            color: #e6e6e6 !important;
            border-color: #3a3a5e !important;
        }

        body.dark-theme .rename-input:focus {
            border-color: #0ea5e9 !important;
        }

        /* Section Titles */
        body.dark-theme .section-title {
            color: #9ca3af !important;
        }

        /* Settings Section */
        body.dark-theme .settings-section {
            border-color: #3a3a5e !important;
        }

        /* Header */
        body.dark-theme .header {
            background: linear-gradient(135deg, #0c4a6e 0%, #075985 50%, #0369a1 100%) !important;
        }

        /* Sidebar Header */
        body.dark-theme .sidebar-header {
            border-color: #3a3a5e !important;
        }

        /* Modal */
        body.dark-theme .modal-header {
            border-color: #3a3a5e !important;
        }

        body.dark-theme .modal-title {
            color: #e6e6e6 !important;
        }

        body.dark-theme .form-label {
            color: #9ca3af !important;
        }

        body.dark-theme .btn-secondary {
            background: #3a3a5e !important;
            color: #e6e6e6 !important;
        }

        body.dark-theme .btn-secondary:hover {
            background: #4a4a6e !important;
        }


        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e0f2fe;
        }

        .modal-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .modal-title {
            font-size: 22px;
            font-weight: 700;
            color: #1e293b;
        }

        .modal-body {
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 8px;
        }

        .form-input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid rgba(14, 165, 233, 0.2);
            border-radius: 10px;
            font-size: 14px;
            outline: none;
            transition: all .3s ease;
        }

        .form-input:focus {
            border-color: #0ea5e9;
            box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.1);
        }

        .modal-footer {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .btn-modal {
            padding: 12px 24px;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all .3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(14, 165, 233, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(14, 165, 233, 0.5);
        }

        .btn-secondary {
            background: #e2e8f0;
            color: #475569;
        }

        .btn-secondary:hover {
            background: #cbd5e1;
        }

        .close-modal {
            position: absolute;
            top: 20px;
            right: 20px;
            background: none;
            border: none;
            font-size: 24px;
            color: #94a3b8;
            cursor: pointer;
            transition: all .2s ease;
        }

        .close-modal:hover {
            color: #475569;
            transform: rotate(90deg);
        }


        /* MENU BUTTON */
        .menu-btn {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            width: 38px;
            height: 38px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all .3s ease;
            font-weight: 300;
            color: white;
            flex-shrink: 0;
        }

        .menu-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.05);
        }

        .menu-btn:active {
            transform: scale(0.95);
        }

        .header {
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 50%, #0369a1 100%);
            padding: 16px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 17px;
            font-weight: 700;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            color: white;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }


        .header-title {
            flex: 1;
            text-align: center;
            margin-right: 38px;
            /* Balance the menu button width */
        }

        /* CHAT BOX */
        .chat-box {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            scroll-behavior: smooth;
            background: #f8fafc;
        }

        .chat-box::-webkit-scrollbar {
            width: 8px;
        }

        .chat-box::-webkit-scrollbar-track {
            background: transparent;
        }

        .chat-box::-webkit-scrollbar-thumb {
            background: rgba(14, 165, 233, 0.3);
            border-radius: 10px;
        }

        .chat-box::-webkit-scrollbar-thumb:hover {
            background: rgba(14, 165, 233, 0.5);
        }

        /* BOT BUBBLE */
        .bot-message {
            background: white;
            backdrop-filter: blur(20px);
            padding: 12px 16px;
            border-radius: 16px 16px 16px 4px;
            width: fit-content;
            max-width: 75%;
            margin-bottom: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            animation: fadeIn .4s ease;
            color: #1e293b;
            line-height: 1.6;
            border: 1px solid rgba(14, 165, 233, 0.1);
            font-size: 13px;
        }

        /* Markdown styling inside bot messages */
        .bot-message p {
            margin: 0 0 8px 0;
        }

        .bot-message p:last-child {
            margin-bottom: 0;
        }

        .bot-message h1,
        .bot-message h2,
        .bot-message h3 {
            margin: 12px 0 8px 0;
            font-weight: 600;
            color: #0369a1;
        }

        .bot-message h1 {
            font-size: 16px;
        }

        .bot-message h2 {
            font-size: 15px;
        }

        .bot-message h3 {
            font-size: 14px;
        }

        .bot-message ul,
        .bot-message ol {
            margin: 8px 0;
            padding-left: 20px;
        }

        .bot-message li {
            margin: 4px 0;
        }

        .bot-message code {
            background: #f1f5f9;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: 'Fira Code', monospace;
            font-size: 12px;
            color: #0369a1;
        }

        .bot-message pre {
            background: #1e293b;
            color: #e2e8f0;
            padding: 12px;
            border-radius: 8px;
            overflow-x: auto;
            margin: 8px 0;
        }

        .bot-message pre code {
            background: transparent;
            color: inherit;
            padding: 0;
        }

        .bot-message blockquote {
            border-left: 3px solid #0ea5e9;
            padding-left: 12px;
            margin: 8px 0;
            color: #64748b;
            font-style: italic;
        }

        .bot-message strong {
            font-weight: 600;
            color: #0369a1;
        }

        .bot-message em {
            font-style: italic;
        }

        .bot-message a {
            color: #0ea5e9;
            text-decoration: underline;
        }

        .bot-message table {
            border-collapse: collapse;
            margin: 8px 0;
            font-size: 12px;
        }

        .bot-message th,
        .bot-message td {
            border: 1px solid #e2e8f0;
            padding: 6px 10px;
        }

        .bot-message th {
            background: #f1f5f9;
            font-weight: 600;
        }



        .sources-container {
            margin: 8px 0 12px 0;
            max-width: 75%;
        }

        .sources-header {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            color: #64748b;
            margin-bottom: 6px;
            font-weight: 500;
        }

        .sources-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .source-chip {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.08) 0%, rgba(2, 132, 199, 0.05) 100%);
            border: 1px solid rgba(14, 165, 233, 0.2);
            border-radius: 20px;
            font-size: 11px;
            color: #0369a1;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .source-chip:hover {
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.15) 0%, rgba(2, 132, 199, 0.1) 100%);
            border-color: rgba(14, 165, 233, 0.4);
            transform: translateY(-1px);
        }

        .source-icon {
            font-size: 12px;
        }

        .source-name {
            font-weight: 500;
        }

        /* Dark mode for sources */
        body.dark-theme .sources-header {
            color: #9ca3af;
        }

        body.dark-theme .source-chip {
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.15) 0%, rgba(2, 132, 199, 0.1) 100%);
            border-color: rgba(14, 165, 233, 0.3);
            color: #7dd3fc;
        }

        body.dark-theme .source-chip:hover {
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.25) 0%, rgba(2, 132, 199, 0.2) 100%);
        }

        /* USER BUBBLE */
        .user-message {
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            color: white;
            padding: 12px 16px;
            border-radius: 16px 16px 4px 16px;
            width: fit-content;
            max-width: 75%;
            margin-bottom: 12px;
            margin-left: auto;
            box-shadow: 0 2px 12px rgba(14, 165, 233, 0.3);
            animation: fadeInRight .4s ease;
            line-height: 1.6;
            font-size: 13px;
            white-space: pre-wrap;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px) scale(0.95);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes fadeInRight {
            from {
                opacity: 0;
                transform: translate(10px, 10px) scale(0.95);
            }

            to {
                opacity: 1;
                transform: translate(0, 0) scale(1);
            }
        }

        /* INPUT AREA */
        .input-area {
            display: flex;
            padding: 16px 20px;
            background: white;
            backdrop-filter: blur(20px);
            gap: 10px;
            border-top: 1px solid rgba(14, 165, 233, 0.1);
            box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.08);
        }

        .chat-input {
            flex: 1;
            padding: 12px 16px;
            border-radius: 16px;
            font-size: 14px;
            border: 2px solid rgba(14, 165, 233, 0.2);
            outline: none;
            transition: all .3s ease;
            background: #f8fafc;
            color: #1e293b;
        }

        .chat-input:focus {
            border-color: #0ea5e9;
            box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.1);
            background: white;
        }

        .send-btn {
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            color: white;
            width: 52px;
            height: 52px;
            border: none;
            font-size: 20px;
            border-radius: 16px;
            cursor: pointer;
            transition: all .3s ease;
            box-shadow: 0 4px 12px rgba(14, 165, 233, 0.4);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .send-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(14, 165, 233, 0.5);
        }

        .send-btn:active {
            transform: translateY(0);
        }

        .send-btn:disabled {
            background: linear-gradient(135deg, #7dd3fc 0%, #67b9e8 100%);
            cursor: not-allowed;
            transform: none;
        }

        /* TYPING INDICATOR */
        .typing-indicator {
            display: flex;
            gap: 6px;
            padding: 12px 16px;
            width: fit-content;
        }

        .typing-dot {
            width: 8px;
            height: 8px;
            background: #0ea5e9;
            border-radius: 50%;
            animation: typing 1.4s infinite;
        }

        .typing-dot:nth-child(2) {
            animation-delay: 0.2s;
        }

        .typing-dot:nth-child(3) {
            animation-delay: 0.4s;
        }

        @keyframes typing {

            0%,
            60%,
            100% {
                transform: translateY(0);
                opacity: 0.7;
            }

            30% {
                transform: translateY(-10px);
                opacity: 1;
            }
        }

        /* ========== MODAL KONFIRMASI ========== */
        .confirm-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 2000;
        }

        .confirm-modal-overlay.show {
            display: flex;
        }

        .confirm-modal {
            background: #fff;
            border-radius: 16px;
            padding: 30px;
            max-width: 320px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: modalSlideIn 0.3s ease;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: scale(0.9) translateY(-20px);
            }

            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .confirm-modal-icon {
            font-size: 48px;
            text-align: center;
            margin-bottom: 15px;
        }

        .confirm-modal-title {
            font-size: 18px;
            font-weight: 700;
            text-align: center;
            color: #1e293b;
            margin-bottom: 10px;
        }

        .confirm-modal-message {
            text-align: center;
            color: #64748b;
            margin-bottom: 25px;
            font-size: 14px;
        }

        .confirm-modal-buttons {
            display: flex;
            gap: 12px;
            justify-content: center;
        }

        .confirm-modal-btn {
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
        }

        .confirm-modal-btn.cancel {
            background: #e2e8f0;
            color: #475569;
        }

        .confirm-modal-btn.cancel:hover {
            background: #cbd5e1;
        }

        .confirm-modal-btn.danger {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: #fff;
        }

        .confirm-modal-btn.danger:hover {
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            body {
                padding: 0;
                align-items: stretch;
                justify-content: center;
            }

            .layout {
                height: 100vh;
                max-height: none;
                width: 100vw;
                max-width: none;
                border-radius: 0;
                border: none;
            }

            .bot-message,
            .user-message {
                max-width: 85%;
                font-size: 14px;
            }

            .header {
                font-size: 15px;
                padding: 14px 16px;
            }

            .chat-box {
                padding: 15px;
            }

            .menu-btn {
                width: 34px;
                height: 34px;
                font-size: 18px;
            }

            .header-title {
                margin-right: 34px;
            }
        }
    </style>
</head>

<body>

    {{-- Modal Konfirmasi --}}
    <div class="confirm-modal-overlay" id="confirmModal">
        <div class="confirm-modal">
            <div class="confirm-modal-icon">⚠️</div>
            <div class="confirm-modal-title" id="confirmModalTitle">Konfirmasi</div>
            <div class="confirm-modal-message" id="confirmModalMessage">Apakah Anda yakin?</div>
            <div class="confirm-modal-buttons">
                <button class="confirm-modal-btn cancel" onclick="closeConfirmModal()">Batal</button>
                <button class="confirm-modal-btn danger" id="confirmModalAction">Ya, Hapus</button>
            </div>
        </div>
    </div>

    {{-- Modal Rename --}}
    <div class="confirm-modal-overlay" id="renameModal">
        <div class="confirm-modal">
            <div class="confirm-modal-icon">✏️</div>
            <div class="confirm-modal-title">Ubah Nama Obrolan</div>
            <input type="text" id="renameInput" class="rename-input" placeholder="Masukkan nama baru">
            <div class="confirm-modal-buttons">
                <button class="confirm-modal-btn cancel" onclick="closeRenameModal()">Batal</button>
                <button class="confirm-modal-btn primary" id="renameModalAction">Simpan</button>
            </div>
        </div>
    </div>

    <div class="layout">

        <!-- SIDEBAR -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">🎓</div>
                <div class="sidebar-title">SIAssist</div>
            </div>

            <button class="btn-sidebar" id="newChatBtn">✨ Obrolan Baru</button>
            <input type="text" id="searchChat" placeholder="🔍 Cari obrolan..." class="search-bar">
            <div class="section-title">Riwayat Obrolan</div>

            <ul class="chat-list" id="chatList">
                @foreach ($chats as $chat)
                <li data-chat="{{ $chat->id }}" data-pinned="{{ $chat->is_pinned ?? false }}"
                    class="{{ isset($active_chat) && $active_chat->id == $chat->id ? 'active' : '' }} {{ ($chat->is_pinned ?? false) ? 'pinned' : '' }}">
                    <span class="chat-item-text" onclick="loadChat('{{ $chat->id }}')">
                        @if($chat->is_pinned ?? false)<span class="pin-indicator">📌</span>@endif
                        {{ $chat->title }}
                    </span>

                    <button class="chat-menu-btn" onclick="toggleChatMenu(event, '{{ $chat->id }}')">⋮</button>

                    <div class="chat-dropdown" id="dropdown-{{ $chat->id }}">
                        <div class="chat-dropdown-item" onclick="pinChat(event, '{{ $chat->id }}')">
                            <span class="menu-icon">📌</span>
                            <span class="pin-text">{{ ($chat->is_pinned ?? false) ? 'Lepas Pin' : 'Pin' }}</span>
                        </div>
                        <div class="chat-dropdown-item" onclick="renameChat(event, '{{ $chat->id }}')">
                            <span class="menu-icon">✏️</span>
                            Rename
                        </div>
                        <div class="chat-dropdown-item danger" onclick="deleteChat(event, '{{ $chat->id }}')">
                            <span class="menu-icon">🗑️</span>
                            Hapus
                        </div>
                    </div>
                </li>
                @endforeach
            </ul>

            <div class="settings-section">
                <div class="section-title">Pengaturan</div>
                <ul>
                    <li onclick="openModal('settingsModal')">⚙️ Settings</li>
                    <li onclick="openModal('accountModal')">👤 Akun</li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                            @csrf
                            <button type="submit" style="
                    background: none; 
                    border: none; 
                    padding: 0;
                    cursor: pointer; 
                    color: inherit; 
                    width: 100%; 
                    text-align: left;
                    display: flex;
                    align-items: center;
                    gap: 10px;
                    font-size: 13px;
                ">
                                🚪 Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </aside>


        <main class="main-chat">
            <header class="header">
                <button class="menu-btn" id="menuBtn">☰</button>
                <span class="header-title">SIAssist - Smart Assistant</span>
            </header>
            <div class="chat-box" id="chatBox">
                @forelse ($messages as $msg)
                @if ($msg->user_message)
                <div class="user-message">{{ $msg->user_message }}</div>
                @endif

                @if ($msg->bot_response)
                <div class="bot-message" data-markdown>{!! $msg->bot_response !!}</div>
                @if ($msg->sources && count($msg->sources) > 0)
                <div class="sources-container">
                    <div class="sources-header">📚 <span>Sumber Referensi</span></div>
                    <div class="sources-chips">
                        @foreach ($msg->sources as $source)
                        @if (!empty($source['doc_id']))
                        <a href="/dokumen/{{ $source['doc_id'] }}/download" target="_blank" class="source-chip"
                            title="Buka: {{ $source['name'] ?? 'Dokumen' }}">
                            <span class="source-icon">📄</span>
                            <span class="source-name">{{ Str::limit($source['name'] ?? 'Dokumen', 30) }}</span>
                        </a>
                        @else
                        <div class="source-chip" title="{{ $source['name'] ?? 'Dokumen' }}">
                            <span class="source-icon">📄</span>
                            <span class="source-name">{{ Str::limit($source['name'] ?? 'Dokumen', 30) }}</span>
                        </div>
                        @endif
                        @endforeach
                    </div>
                </div>
                @endif
                @endif
                @empty
                <div class="bot-message" id="greetingMessage">Halo! Ada yang bisa saya bantu hari ini? 😊</div>
                @endforelse
            </div>

            <div class="input-area">
                <input type="text" id="messageInput" class="chat-input" placeholder="Ketik pesan Anda di sini...">
                <button class="send-btn" id="sendBtn">➤</button>
            </div>
        </main>

    </div>


    <!-- Source Viewer Modal -->
    <div id="sourceViewerModal" class="modal">
        <div class="modal-content" style="max-width: 700px;">
            <button class="close-modal" onclick="closeModal('sourceViewerModal')">×</button>
            <div class="modal-header">
                <div class="modal-icon">📄</div>
                <h2 class="modal-title" id="sourceViewerTitle">Dokumen</h2>
            </div>
            <div class="modal-body">
                <div id="sourceViewerContent"
                    style="max-height: 400px; overflow-y: auto; padding: 15px; background: #f8fafc; border-radius: 8px; font-size: 14px; line-height: 1.6; white-space: pre-wrap;">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-modal btn-secondary" onclick="closeModal('sourceViewerModal')">Tutup</button>
            </div>
        </div>
    </div>

    <div id="settingsModal" class="modal">
        <div class="modal-content">
            <button class="close-modal" onclick="closeModal('settingsModal')">×</button>
            <div class="modal-header">
                <div class="modal-icon">⚙️</div>
                <h2 class="modal-title">Pengaturan</h2>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Tema</label>
                    <select class="form-input">
                        <option>Terang</option>
                        <option>Gelap</option>
                        <option>Otomatis</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Notifikasi</label>
                    <select class="form-input">
                        <option>Aktif</option>
                        <option>Nonaktif</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-modal btn-secondary" onclick="closeModal('settingsModal')">Batal</button>
                <button class="btn-modal btn-primary" onclick="saveSettings()">Simpan</button>
            </div>
        </div>
    </div>


    <div id="accountModal" class="modal">
        <div class="modal-content">
            <button class="close-modal" onclick="closeModal('accountModal')">×</button>
            <div class="modal-header">
                <div class="modal-icon">👤</div>
                <h2 class="modal-title">Akun Saya</h2>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" id="accountName" class="form-input" placeholder="Masukkan nama lengkap">
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" id="accountEmail" class="form-input" placeholder="Masukkan email">
                </div>
                <div class="form-group">
                    <label class="form-label">NIM/NIDN</label>
                    <input type="text" id="accountNimNidn" class="form-input"
                        placeholder="NIM (mahasiswa) atau NIDN (dosen)">
                </div>
                <div class="form-group">
                    <label class="form-label">Password Baru (opsional)</label>
                    <input type="password" id="accountPassword" class="form-input" placeholder="Masukkan password baru">
                </div>
                <div id="accountMessage" style="display: none; padding: 10px; border-radius: 8px; margin-top: 10px;">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-modal btn-secondary" onclick="closeModal('accountModal')">Batal</button>
                <button class="btn-modal btn-primary" onclick="saveAccount()">Simpan</button>
            </div>
        </div>
    </div>

    <script>
        const sidebar = document.getElementById("sidebar");
        const chatBox = document.getElementById("chatBox");
        const input = document.getElementById("messageInput");
        const sendBtn = document.getElementById("sendBtn");
        const newChatBtn = document.getElementById("newChatBtn");
        const historyList = document.getElementById("historyList");
        const searchChat = document.getElementById("searchChat");
        const menuBtn = document.getElementById("menuBtn");
        const layout = document.querySelector(".layout");

        // === SIDEBAR ===
        function toggleSidebar() {
            sidebar.classList.toggle("show");
            layout.classList.toggle("sidebar-open");
        }

        function closeSidebar() {
            sidebar.classList.remove("show");
            layout.classList.remove("sidebar-open");
        }

        menuBtn.addEventListener("click", toggleSidebar);

        // Close sidebar when clicking on overlay (main content area when sidebar is open)
        document.addEventListener("click", (e) => {
            if (layout.classList.contains("sidebar-open")) {
                if (!sidebar.contains(e.target) && !menuBtn.contains(e.target)) {
                    closeSidebar();
                }
            }
        });

        // === MODAL ===
        function openModal(id) {
            document.getElementById(id).classList.add("show");
        }

        function closeModal(id) {
            document.getElementById(id).classList.remove("show");
        }

        // === MODAL KONFIRMASI ===
        let confirmCallback = null;

        function showConfirmModal(title, message, callback) {
            document.getElementById('confirmModalTitle').textContent = title;
            document.getElementById('confirmModalMessage').textContent = message;
            document.getElementById('confirmModal').classList.add('show');
            confirmCallback = callback;
        }

        function closeConfirmModal() {
            document.getElementById('confirmModal').classList.remove('show');
            confirmCallback = null;
        }

        document.getElementById('confirmModalAction').addEventListener('click', function() {
            const callback = confirmCallback;
            closeConfirmModal();
            if (callback) callback();
        });

        document.getElementById('confirmModal').addEventListener('click', function(e) {
            if (e.target === this) closeConfirmModal();
        });

        // === MODAL RENAME ===
        let renameCallback = null;
        let renameChatId = null;

        function showRenameModal(chatId, currentTitle, callback) {
            document.getElementById('renameInput').value = currentTitle;
            document.getElementById('renameModal').classList.add('show');
            document.getElementById('renameInput').focus();
            renameChatId = chatId;
            renameCallback = callback;
        }

        function closeRenameModal() {
            document.getElementById('renameModal').classList.remove('show');
            renameCallback = null;
            renameChatId = null;
        }

        document.getElementById('renameModalAction').addEventListener('click', function() {
            const newTitle = document.getElementById('renameInput').value.trim();
            if (newTitle && renameCallback) {
                renameCallback(newTitle);
            }
            closeRenameModal();
        });

        document.getElementById('renameInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.getElementById('renameModalAction').click();
            }
        });

        document.getElementById('renameModal').addEventListener('click', function(e) {
            if (e.target === this) closeRenameModal();
        });

        function saveSettings() {
            alert('Pengaturan disimpan!');
            closeModal('settingsModal');
        }

        function saveAccount() {
            alert('Akun diperbarui!');
            closeModal('accountModal');
        }
        window.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal')) e.target.classList.remove('show');
        });

        // === CHAT ===
        // === CHAT MENU FUNCTIONS ===
        let activeDropdown = null;

        function toggleChatMenu(event, chatId) {
            event.stopPropagation();

            const chatList = document.getElementById('chatList');
            const li = document.querySelector(`li[data-chat="${chatId}"]`);

            // Close any open dropdown
            if (activeDropdown && activeDropdown !== chatId) {
                document.getElementById('dropdown-' + activeDropdown)?.classList.remove('show');
                document.querySelector(`li[data-chat="${activeDropdown}"]`)?.classList.remove('menu-open');
            }

            const dropdown = document.getElementById('dropdown-' + chatId);
            const isOpening = !dropdown.classList.contains('show');

            dropdown.classList.toggle('show');
            li.classList.toggle('menu-open');

            if (isOpening) {
                chatList.classList.add('menu-active');
                activeDropdown = chatId;
            } else {
                chatList.classList.remove('menu-active');
                activeDropdown = null;
            }
        }

        function closeAllMenus() {
            const chatList = document.getElementById('chatList');
            document.querySelectorAll('.chat-dropdown').forEach(d => d.classList.remove('show'));
            document.querySelectorAll('.chat-list li').forEach(li => li.classList.remove('menu-open'));
            chatList?.classList.remove('menu-active');
            activeDropdown = null;
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.chat-dropdown') && !e.target.closest('.chat-menu-btn')) {
                closeAllMenus();
            }
        });

        async function pinChat(event, chatId) {
            event.stopPropagation();
            const chatList = document.getElementById('chatList');
            const li = document.querySelector(`li[data-chat="${chatId}"]`);
            const isPinned = li.classList.contains('pinned');

            try {
                const response = await fetch(`/chat/${chatId}/pin`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        pinned: !isPinned
                    })
                });

                if (response.ok) {
                    // Toggle UI
                    li.classList.toggle('pinned');
                    const pinText = li.querySelector('.pin-text');
                    const chatText = li.querySelector('.chat-item-text');

                    if (li.classList.contains('pinned')) {
                        pinText.textContent = 'Lepas Pin';
                        if (!chatText.querySelector('.pin-indicator')) {
                            chatText.insertAdjacentHTML('afterbegin', '<span class="pin-indicator">📌</span>');
                        }
                        // Move to top of list
                        chatList.insertBefore(li, chatList.firstChild);
                    } else {
                        pinText.textContent = 'Pin';
                        chatText.querySelector('.pin-indicator')?.remove();
                        // Move after all pinned items
                        const firstNonPinned = chatList.querySelector('li:not(.pinned)');
                        if (firstNonPinned) {
                            chatList.insertBefore(li, firstNonPinned);
                        }
                    }
                }
            } catch (error) {
                console.error('Pin error:', error);
            }

            // Close dropdown
            closeAllMenus();
        }

        function renameChat(event, chatId) {
            event.stopPropagation();
            closeAllMenus();

            const li = document.querySelector(`li[data-chat="${chatId}"]`);
            const chatText = li.querySelector('.chat-item-text');
            const currentTitle = chatText.textContent.replace('📌', '').trim();

            showRenameModal(chatId, currentTitle, function(newTitle) {
                if (newTitle !== currentTitle) {
                    fetch(`/chat/${chatId}/rename`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            title: newTitle
                        })
                    }).then(response => {
                        if (response.ok) {
                            const pinIndicator = chatText.querySelector('.pin-indicator');
                            chatText.textContent = newTitle;
                            if (pinIndicator) {
                                chatText.insertAdjacentHTML('afterbegin', '<span class="pin-indicator">📌</span>');
                            }
                        }
                    }).catch(console.error);
                }
            });
        }

        function deleteChat(event, chatId) {
            event.stopPropagation();
            closeAllMenus();

            showConfirmModal('Hapus Obrolan', 'Apakah Anda yakin ingin menghapus obrolan ini?', function() {
                fetch(`/chat/${chatId}/delete`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                }).then(response => {
                    if (response.ok) {
                        document.querySelector(`li[data-chat="${chatId}"]`).remove();
                        if (window.location.pathname === `/chat/${chatId}`) {
                            window.location.href = '/chat';
                        }
                    }
                }).catch(console.error);
            });
        }

        // === HELPER FUNCTIONS ===
        function addMessage(text, sender) {
            const bubble = document.createElement("div");
            bubble.className = sender === "user" ? "user-message" : "bot-message";

            if (sender === "bot" && typeof marked !== 'undefined') {
                // Parse markdown for bot messages
                bubble.innerHTML = marked.parse(text);
            } else {
                bubble.textContent = text;
            }

            chatBox.appendChild(bubble);
            chatBox.scrollTop = chatBox.scrollHeight;
        }

        // Add new chat to sidebar with active class
        function addNewChatToSidebar(chatId, title) {
            const chatList = document.getElementById('chatList');

            // Remove active class from all existing chats
            chatList.querySelectorAll('li.active').forEach(li => li.classList.remove('active'));

            // Create new chat item HTML
            const newChatHtml = `
                <li data-chat="${chatId}" data-pinned="false" class="active">
                    <span class="chat-item-text" onclick="loadChat('${chatId}')">${title}</span>
                    <button class="chat-menu-btn" onclick="toggleChatMenu(event, '${chatId}')">⋮</button>
                    <div class="chat-dropdown" id="dropdown-${chatId}">
                        <div class="chat-dropdown-item" onclick="pinChat(event, '${chatId}')">
                            <span class="menu-icon">📌</span>
                            <span class="pin-text">Pin</span>
                        </div>
                        <div class="chat-dropdown-item" onclick="renameChat(event, '${chatId}')">
                            <span class="menu-icon">✏️</span>
                            Rename
                        </div>
                        <div class="chat-dropdown-item danger" onclick="deleteChat(event, '${chatId}')">
                            <span class="menu-icon">🗑️</span>
                            Hapus
                        </div>
                    </div>
                </li>
            `;

            // Insert at top of list (after any pinned items)
            const firstUnpinned = chatList.querySelector('li:not(.pinned)');
            if (firstUnpinned) {
                firstUnpinned.insertAdjacentHTML('beforebegin', newChatHtml);
            } else {
                chatList.insertAdjacentHTML('afterbegin', newChatHtml);
            }
        }

        // Display sources like modern LLMs (ChatGPT/Perplexity style)
        function addSourcesDisplay(sources) {
            const container = document.createElement("div");
            container.className = "sources-container";

            const header = document.createElement("div");
            header.className = "sources-header";
            header.innerHTML = '📚 <span>Sumber Referensi</span>';
            container.appendChild(header);

            const chipContainer = document.createElement("div");
            chipContainer.className = "sources-chips";

            sources.forEach((source, index) => {
                // Extract filename for display
                const fileName = source.name || `Dokumen ${index + 1}`;
                const displayName = fileName.length > 30 ? fileName.substring(0, 27) + '...' : fileName;
                const docId = source.doc_id;

                // Create anchor or div based on whether we have doc_id
                if (docId) {
                    const link = document.createElement("a");
                    link.className = "source-chip";
                    link.href = `/dokumen/${docId}/download`;
                    link.target = "_blank";
                    link.title = `Buka: ${fileName}`;
                    link.innerHTML = `
                        <span class="source-icon">📄</span>
                        <span class="source-name">${displayName}</span>
                    `;
                    chipContainer.appendChild(link);
                } else {
                    const chip = document.createElement("div");
                    chip.className = "source-chip";
                    chip.title = fileName;
                    chip.innerHTML = `
                        <span class="source-icon">📄</span>
                        <span class="source-name">${displayName}</span>
                    `;
                    chipContainer.appendChild(chip);
                }
            });

            container.appendChild(chipContainer);
            chatBox.appendChild(container);
            chatBox.scrollTop = chatBox.scrollHeight;
        }

        function showTypingIndicator() {
            const typing = document.createElement("div");
            typing.className = "typing-indicator";
            typing.id = "typingIndicator";
            typing.innerHTML = '<div class="typing-dot"></div><div class="typing-dot"></div><div class="typing-dot"></div>';
            chatBox.appendChild(typing);
            chatBox.scrollTop = chatBox.scrollHeight;
        }

        function hideTypingIndicator() {
            const typing = document.getElementById("typingIndicator");
            if (typing) typing.remove();
        }

        function loadChat(id) {
            window.location.href = "/chat/" + id;
        }


        // === Kirim Pesan ke Backend ===
        // Get current chat ID from URL
        let currentChatId = window.location.pathname.split('/').pop();

        // Check if currentChatId is valid (number)
        if (isNaN(currentChatId) || currentChatId === 'chat') {
            currentChatId = null;
        }

        // Flag untuk clear greeting message saat pesan pertama
        let isFirstMessage = !currentChatId;

        async function sendMessage() {
            const message = input.value.trim();
            if (!message) return;

            // Clear greeting message jika ini pesan pertama
            if (isFirstMessage) {
                const greeting = document.getElementById('greetingMessage');
                if (greeting) greeting.remove();
                isFirstMessage = false;
            }

            addMessage(message, "user");
            input.value = "";
            sendBtn.disabled = true;

            showTypingIndicator();

            try {
                // Jika belum ada chat, buat dulu
                if (!currentChatId) {
                    const newChatResponse = await fetch("/chat/new", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({})
                    });

                    const newChatData = await newChatResponse.json();
                    currentChatId = newChatData.chat_id;

                    // Update URL tanpa reload (untuk history browser)
                    window.history.pushState({}, '', `/chat/${currentChatId}`);

                    // Add new chat to sidebar with active class
                    addNewChatToSidebar(currentChatId, 'Obrolan Baru');
                }

                // Kirim pesan dengan timeout 200 detik (untuk model LLM besar seperti Qwen 8B)
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 200000); // 200 detik

                const response = await fetch(`/chat/${currentChatId}/send`, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        chat_id: currentChatId,
                        message: message
                    }),
                    signal: controller.signal
                });

                clearTimeout(timeoutId);

                const data = await response.json();
                hideTypingIndicator();

                if (data.reply) {
                    addMessage(data.reply, "bot");

                    // Display sources if available (like modern LLMs)
                    if (data.sources && data.sources.length > 0) {
                        addSourcesDisplay(data.sources);
                    }

                    // Update sidebar title jika ada title baru
                    if (data.title) {
                        const chatItem = document.querySelector(`li[data-chat="${currentChatId}"] .chat-item-text`);
                        if (chatItem) {
                            chatItem.textContent = data.title;
                        } else {
                            // Jika chat baru, reload untuk update sidebar
                            window.location.href = `/chat/${currentChatId}`;
                        }
                    }
                } else {
                    addMessage("⚠️ Tidak ada respons dari server.", "bot");
                }
            } catch (error) {
                hideTypingIndicator();

                // Bedakan antara timeout dan error lainnya
                if (error.name === 'AbortError') {
                    addMessage("⏱️ Server terlalu lama merespons (timeout). Silakan coba lagi.", "bot");
                } else {
                    addMessage("❌ Terjadi kesalahan koneksi ke server.", "bot");
                }
                console.error(error);
            } finally {
                sendBtn.disabled = false;
            }
        }

        // === Event Listener ===
        sendBtn.addEventListener("click", sendMessage);
        input.addEventListener("keydown", (e) => {
            if (e.key === "Enter") sendMessage();
        });

        document.getElementById("newChatBtn").addEventListener("click", () => {
            // Hanya reset UI, tidak buat chat di database
            // Chat akan dibuat saat pesan pertama dikirim
            currentChatId = null;
            isFirstMessage = true;

            // Clear chat box dan tampilkan greeting
            chatBox.innerHTML = '<div class="bot-message" id="greetingMessage">Halo! Ada yang bisa saya bantu hari ini? 😊</div>';

            // Update URL ke /chat (tanpa ID)
            window.history.pushState({}, '', '/chat');

            // Remove active class from all chats in sidebar
            document.querySelectorAll('#chatList li.active').forEach(li => li.classList.remove('active'));

            // Tutup sidebar
            closeSidebar();
        });


        searchChat.addEventListener("input", () => {
            const filter = searchChat.value.toLowerCase();
            const chatList = document.getElementById('chatList');
            Array.from(chatList.getElementsByTagName("li")).forEach(chat => {
                const text = chat.querySelector('.chat-item-text')?.textContent.toLowerCase() || '';
                chat.style.display = text.includes(filter) ? "" : "none";
            });
        });
    </script>


</body>
<script>
    // ========== OPEN dan CLOSE MODAL ==========
    function openModal(id) {
        document.getElementById(id).style.display = "flex";
    }

    function closeModal(id) {
        document.getElementById(id).style.display = "none";
    }

    // ========== APPLY THEME ==========
    function applyTheme(mode) {
        document.body.classList.remove("light-theme", "dark-theme");

        if (mode === "Terang") {
            document.body.classList.remove("dark-theme");
        } else if (mode === "Gelap") {
            document.body.classList.add("dark-theme");
        } else if (mode === "Otomatis") {
            const prefersDark = window.matchMedia("(prefers-color-scheme: dark)").matches;
            if (prefersDark) {
                document.body.classList.add("dark-theme");
            }
        }
    }

    // ========== SAVE SETTINGS ==========
    function saveSettings() {
        const theme = document.querySelector("#settingsModal select:nth-of-type(1)").value;

        localStorage.setItem("theme_mode", theme);

        applyTheme(theme);
        closeModal('settingsModal');
    }

    // ========== LOAD SETTINGS ON PAGE LOAD ==========
    function loadTheme() {
        const savedTheme = localStorage.getItem("theme_mode") || "Terang";

        const themeSelect = document.querySelector("#settingsModal select:nth-of-type(1)");
        if (themeSelect) themeSelect.value = savedTheme;
        applyTheme(savedTheme);
    }

    // ========== SAVE ACCOUNT ==========
    async function saveAccount() {
        const name = document.getElementById('accountName').value;
        const email = document.getElementById('accountEmail').value;
        const nim_nidn = document.getElementById('accountNimNidn').value;
        const password = document.getElementById('accountPassword').value;
        const messageDiv = document.getElementById('accountMessage');

        try {
            const response = await fetch('/account', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    name,
                    email,
                    nim_nidn,
                    password: password || null
                })
            });

            const data = await response.json();

            if (response.ok) {
                messageDiv.style.display = 'block';
                messageDiv.style.background = '#dcfce7';
                messageDiv.style.color = '#166534';
                messageDiv.textContent = data.message || 'Akun berhasil diperbarui!';

                // Clear password field
                document.getElementById('accountPassword').value = '';

                setTimeout(() => {
                    closeModal('accountModal');
                    messageDiv.style.display = 'none';
                }, 1500);
            } else {
                messageDiv.style.display = 'block';
                messageDiv.style.background = '#fee2e2';
                messageDiv.style.color = '#991b1b';
                messageDiv.textContent = data.message || 'Gagal memperbarui akun.';
            }
        } catch (error) {
            console.error('Error saving account:', error);
            messageDiv.style.display = 'block';
            messageDiv.style.background = '#fee2e2';
            messageDiv.style.color = '#991b1b';
            messageDiv.textContent = 'Terjadi kesalahan. Coba lagi.';
        }
    }

    // ========== LOAD ACCOUNT ==========
    async function loadAccount() {
        try {
            const response = await fetch('/account', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            });

            if (response.ok) {
                const data = await response.json();
                document.getElementById('accountName').value = data.name || '';
                document.getElementById('accountEmail').value = data.email || '';
                document.getElementById('accountNimNidn').value = data.nim_nidn || '';
            }
        } catch (error) {
            console.error('Error loading account:', error);
        }
    }

    // Parse markdown for existing bot messages
    function parseExistingMarkdown() {
        if (typeof marked !== 'undefined') {
            document.querySelectorAll('.bot-message[data-markdown]').forEach(el => {
                const rawText = el.textContent;
                el.innerHTML = marked.parse(rawText);
                el.removeAttribute('data-markdown');
            });
        }
    }

    // Jalankan saat halaman dibuka
    window.onload = function() {
        loadTheme();
        loadAccount();
        parseExistingMarkdown();
    };
</script>

</html>
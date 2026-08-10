<style>
    :root.dark .signature-pad__canvas {
        filter: invert(1);
    }

    :root.dark .signature-preview__image,
    .dark .signature-preview__image {
        filter: brightness(0) invert(1);
    }

    /* Dashboard: keep the information hierarchy calm, compact, and responsive. */
    .dashboard-page {
        --dashboard-accent: rgb(245 158 11);
    }

    .dashboard-page .dashboard-filter-card {
        border: 1px solid rgb(229 231 235 / 0.9);
        box-shadow: 0 10px 30px rgb(15 23 42 / 0.04);
    }

    .dark .dashboard-page .dashboard-filter-card {
        border-color: rgb(63 63 70 / 0.9);
        box-shadow: 0 12px 32px rgb(0 0 0 / 0.16);
    }

    .dashboard-page .fi-wi-stats-overview {
        gap: 1rem;
    }

    .dashboard-page .fi-wi-stats-overview-stat {
        position: relative;
        min-height: 8.25rem;
        overflow: hidden;
        border: 1px solid rgb(229 231 235 / 0.9);
        box-shadow: 0 8px 24px rgb(15 23 42 / 0.04);
        transition: transform 160ms ease, box-shadow 160ms ease;
    }

    .dashboard-page .fi-wi-stats-overview-stat::before {
        position: absolute;
        inset-block: 0;
        inset-inline-start: 0;
        width: 0.25rem;
        background: var(--dashboard-accent);
        content: '';
    }

    .dashboard-page .fi-wi-stats-overview-stat:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 28px rgb(15 23 42 / 0.09);
    }

    .dark .dashboard-page .fi-wi-stats-overview-stat {
        border-color: rgb(63 63 70 / 0.9);
        box-shadow: 0 10px 28px rgb(0 0 0 / 0.16);
    }

    .dashboard-page .fi-wi-chart > .fi-section {
        height: 100%;
    }

    .dashboard-page .fi-wi-chart .fi-section-content {
        min-height: 20rem;
    }

    .dashboard-page .dashboard-ranking-widget > .fi-section {
        height: auto;
    }

    .dashboard-page .dashboard-ranking-widget .fi-section-content {
        min-height: 0;
    }

    .dashboard-ranking-icon {
        display: grid;
        width: 2.5rem;
        height: 2.5rem;
        place-items: center;
        border-radius: 0.75rem;
    }

    .dashboard-ranking-icon--amber {
        color: rgb(180 83 9);
        background: rgb(254 243 199);
    }

    .dashboard-ranking-icon--blue {
        color: rgb(29 78 216);
        background: rgb(219 234 254);
    }

    .dark .dashboard-ranking-icon--amber {
        color: rgb(253 230 138);
        background: rgb(120 53 15 / 0.45);
    }

    .dark .dashboard-ranking-icon--blue {
        color: rgb(147 197 253);
        background: rgb(30 64 175 / 0.35);
    }

    .dashboard-ranking-period {
        border: 1px solid rgb(229 231 235);
        border-radius: 999px;
        padding: 0.3rem 0.65rem;
        color: rgb(75 85 99);
        font-size: 0.75rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .dark .dashboard-ranking-period {
        border-color: rgb(63 63 70);
        color: rgb(161 161 170);
    }

    .dashboard-ranking-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
    }

    .dashboard-ranking-title {
        min-width: 0;
        flex: 1 1 auto;
    }

    .dashboard-ranking-filters {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: 0.5rem;
        flex: 0 0 auto;
    }

    .dashboard-ranking-filters .fi-input-wrp {
        min-width: 7rem;
    }

    .dashboard-ranking-filters select {
        min-width: 6.5rem;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .dashboard-ranking-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        text-align: left;
    }

    .dashboard-ranking-table__rank {
        width: 6.5rem;
    }

    .dashboard-ranking-table__count {
        width: 6.5rem;
        text-align: right;
    }

    .dashboard-ranking-table__name {
        width: auto;
    }

    .dashboard-ranking-table th {
        border-bottom: 1px solid rgb(229 231 235);
        padding: 0 0.75rem 0.7rem;
        color: rgb(107 114 128);
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .dashboard-ranking-table td {
        border-bottom: 1px solid rgb(243 244 246);
        padding: 0.8rem 0.75rem;
        vertical-align: middle;
    }

    .dashboard-ranking-table__rank .dashboard-rank-badge {
        margin-inline: 0.1rem;
    }

    .dashboard-ranking-person {
        min-width: 0;
    }

    .dashboard-ranking-name {
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .dashboard-ranking-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .dashboard-ranking-table tbody tr:hover td {
        background: rgb(249 250 251);
    }

    .dark .dashboard-ranking-table th {
        border-color: rgb(63 63 70);
        color: rgb(161 161 170);
    }

    .dark .dashboard-ranking-table td {
        border-color: rgb(39 39 42);
    }

    .dark .dashboard-ranking-table tbody tr:hover td {
        background: rgb(39 39 42 / 0.55);
    }

    .dashboard-rank-badge {
        display: inline-grid;
        width: 1.75rem;
        height: 1.75rem;
        place-items: center;
        border-radius: 0.5rem;
        color: rgb(107 114 128);
        background: rgb(243 244 246);
        font-size: 0.75rem;
        font-weight: 700;
    }

    .dashboard-rank-badge.is-first {
        color: rgb(146 64 14);
        background: rgb(254 243 199);
    }

    .dark .dashboard-rank-badge {
        color: rgb(212 212 216);
        background: rgb(63 63 70);
    }

    .dark .dashboard-rank-badge.is-first {
        color: rgb(253 230 138);
        background: rgb(120 53 15 / 0.55);
    }

    .dashboard-avatar {
        display: inline-grid;
        width: 2rem;
        height: 2rem;
        place-items: center;
        flex: 0 0 auto;
        border-radius: 999px;
        color: rgb(120 53 15);
        background: rgb(253 230 138);
        font-size: 0.75rem;
        font-weight: 700;
    }

    .dark .dashboard-avatar {
        color: rgb(254 243 199);
        background: rgb(146 64 14);
    }

    .dashboard-report-count {
        display: inline-flex;
        min-width: 2rem;
        justify-content: center;
        border-radius: 999px;
        padding: 0.25rem 0.55rem;
        color: rgb(146 64 14);
        background: rgb(255 247 237);
        font-size: 0.8rem;
        font-weight: 700;
    }

    .dark .dashboard-report-count {
        color: rgb(253 186 116);
        background: rgb(124 45 18 / 0.4);
    }

    /* Report view: keep short metadata scannable and long narrative content readable. */
    .report-infolist-section {
        overflow: hidden;
        border: 1px solid rgb(229 231 235 / 0.9);
        box-shadow: 0 8px 24px rgb(15 23 42 / 0.04);
    }

    .report-infolist-section .fi-section-header {
        border-bottom: 1px solid rgb(243 244 246);
    }

    .report-infolist-section .fi-section-content-ctn {
        padding-block: 1.25rem;
    }

    .report-infolist-section .fi-in-entry-label {
        color: rgb(107 114 128);
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .report-infolist-section .fi-in-entry-content {
        max-width: none;
        overflow-wrap: anywhere;
    }

    .report-infolist-value .fi-in-entry-content {
        color: rgb(17 24 39);
        line-height: 1.55;
    }

    .report-infolist-value--long .fi-in-entry-content {
        line-height: 1.75;
    }

    .report-infolist-rich-text .fi-in-entry-content-ctn {
        width: 100%;
        max-width: none;
    }

    .report-infolist-rich-text .filament-display-how {
        width: 100%;
        max-width: none;
        border: 1px solid rgb(229 231 235);
        border-radius: 0.75rem;
        padding: 1.25rem 1.5rem;
        background: rgb(249 250 251 / 0.7);
    }

    .report-infolist-section--documentation .report-infolist-media,
    .report-infolist-section--signature .report-infolist-signature {
        border-radius: 0.75rem;
        object-fit: cover;
    }

    .report-infolist-section--signature .fi-in-image {
        display: block;
        width: min(100%, 28rem);
        border: 1px solid rgb(229 231 235);
        border-radius: 0.75rem;
        background: rgb(249 250 251);
        overflow: hidden;
    }

    .report-infolist-section--signature .report-infolist-signature {
        display: block;
        width: 100%;
        object-fit: contain;
        background: transparent;
    }

    .dark .report-infolist-section {
        border-color: rgb(63 63 70 / 0.9);
        box-shadow: 0 10px 28px rgb(0 0 0 / 0.16);
    }

    .dark .report-infolist-section .fi-section-header {
        border-color: rgb(63 63 70);
    }

    .dark .report-infolist-section .fi-in-entry-label {
        color: rgb(161 161 170);
    }

    .dark .report-infolist-value .fi-in-entry-content {
        color: rgb(244 244 245);
    }

    .dark .report-infolist-rich-text .filament-display-how {
        border-color: rgb(63 63 70);
        background: rgb(24 24 27 / 0.55);
    }

    .dark .report-infolist-section--signature .fi-in-image {
        border-color: rgb(63 63 70);
        background: rgb(24 24 27);
    }

    @media (max-width: 640px) {
        .dashboard-ranking-header {
            flex-direction: column;
            align-items: stretch;
            gap: 0.75rem;
        }

        .dashboard-ranking-filters {
            width: 100%;
            justify-content: flex-start;
        }

        .dashboard-ranking-filters .fi-input-wrp {
            flex: 1 1 8rem;
        }

        .dashboard-page .fi-wi-chart .fi-section-content {
            min-height: 0;
        }

        .dashboard-page .dashboard-ranking-widget .fi-section-content {
            min-height: 0;
        }

        .dashboard-ranking-table th,
        .dashboard-ranking-table td {
            padding-inline: 0.5rem;
        }

        .dashboard-ranking-table th:first-child,
        .dashboard-ranking-table td:first-child {
            padding-inline-start: 0;
        }

        .dashboard-ranking-table th:last-child,
        .dashboard-ranking-table td:last-child {
            padding-inline-end: 0;
        }
    }
</style>

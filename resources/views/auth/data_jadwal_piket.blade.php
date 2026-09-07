<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daftar Jadwal Piket</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  
  <style>
    /* Reset & Base */
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: 'Inter', sans-serif;
    }

    body {
      background: #e5e5e5;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
    }

    /* daftar-jadwal */
    .daftar-jadwal {
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      align-items: flex-start;
      padding: 0px;
      position: relative;
      width: 390px;
      height: 844px;
      background: #F4F6F9;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0px 10px 25px rgba(0, 0, 0, 0.1);
    }

    /* Frame Utama */
    .frame-main {
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      padding: 0px;
      width: 390px;
    }

    /* status-bar */
    .status-bar {
      display: flex;
      flex-direction: row;
      justify-content: space-between;
      align-items: center;
      padding: 14px 24px 0px;
      width: 390px;
      height: 44px;
      flex: none;
      order: 0;
      align-self: stretch;
    }

    .status-time {
      width: 30px;
      height: 17px;
      font-style: normal;
      font-weight: 600;
      font-size: 14px;
      line-height: 17px;
      color: #1B2A4A;
    }

    .status-icons {
      display: flex;
      flex-direction: row;
      align-items: center;
      padding: 0px;
      gap: 6px;
      width: 68px;
      height: 20px;
    }

    .ios-signal, .ios-wifi {
      width: 16px;
      height: 16px;
      background: #1B2A4A;
      border-radius: 2px;
    }

    .ios-battery {
      width: 24px;
      height: 12px;
      background: #1B2A4A;
      border-radius: 3px;
    }

    /* screen-header */
    .screen-header {
      display: flex;
      flex-direction: row;
      justify-content: space-between;
      align-items: center;
      padding: 16px 24px;
      width: 390px;
      height: 74px;
      flex: none;
      order: 1;
      align-self: stretch;
    }

    .header-text-frame {
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      padding: 0px;
      gap: 2px;
      width: 308px;
      height: 42px;
    }

    .header-title {
      width: 308px;
      height: 24px;
      font-style: normal;
      font-weight: 700;
      font-size: 20px;
      line-height: 24px;
      color: #1B2A4A;
    }

    .header-subtitle {
      width: 308px;
      height: 16px;
      font-style: normal;
      font-weight: 400;
      font-size: 13px;
      line-height: 16px;
      color: #4A5568;
    }

    .back-btn {
      display: flex;
      flex-direction: row;
      justify-content: center;
      align-items: center;
      padding: 8px;
      width: 34px;
      height: 34px;
      background: #E2E8F0;
      border-radius: 100px;
      border: none;
      cursor: pointer;
    }

    .back-btn svg {
      width: 18px;
      height: 18px;
      fill: #1B2A4A;
    }

    /* Frame Konten */
    .content-frame {
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      padding: 0px 24px 20px;
      gap: 16px;
      width: 390px;
      height: 690px;
      overflow-y: auto;
      flex: none;
      order: 2;
      align-self: stretch;
    }

    /* Search Frame */
    .search-frame {
      display: flex;
      flex-direction: row;
      align-items: center;
      padding: 10px 14px;
      gap: 8px;
      width: 342px;
      height: 36px;
      background: #E2E8F0;
      border-radius: 10px;
      flex: none;
      order: 0;
      align-self: stretch;
    }

    .icon-search svg {
      width: 16px;
      height: 16px;
      stroke: #4A5568;
    }

    .search-input {
      width: 290px;
      height: 16px;
      font-style: normal;
      font-weight: 400;
      font-size: 13px;
      line-height: 16px;
      color: #4A5568;
      border: none;
      background: transparent;
      outline: none;
    }

    .search-input::placeholder {
      color: #4A5568;
    }

    /* Tab Filter Hari */
    .day-tabs-frame {
      box-sizing: border-box;
      display: flex;
      flex-direction: row;
      align-items: flex-start;
      padding: 0px;
      gap: 8px;
      width: 342px;
      height: 28px;
      background: #FFFFFF;
      box-shadow: 0px 4px 12px rgba(27, 42, 74, 0.06);
      border-radius: 8px;
      flex: none;
      order: 1;
      align-self: stretch;
    }

    .tab-item {
      display: flex;
      flex-direction: row;
      justify-content: center;
      align-items: center;
      padding: 0px;
      width: 62px;
      height: 28px;
      background: #FFFFFF;
      border-radius: 8px;
      border: none;
      font-style: normal;
      font-weight: 600;
      font-size: 11px;
      line-height: 13px;
      color: #4A5568;
      cursor: pointer;
      flex: 1;
    }

    .tab-item.active {
      background: #1B2A4A;
      color: #FFFFFF;
    }

    /* Tombol Tambah Jadwal */
    .tambah-jadwal-frame {
      display: flex;
      flex-direction: row;
      align-items: center;
      justify-content: space-between;
      padding: 8px 12px;
      gap: 6px;
      width: 342px;
      height: 29px;
      background: #1B2A4A;
      border-radius: 8px;
      border: none;
      cursor: pointer;
      flex: none;
      order: 2;
      align-self: stretch;
    }

    .tambah-jadwal-text {
      font-style: normal;
      font-weight: 700;
      font-size: 11px;
      line-height: 13px;
      color: #FFFFFF;
    }

    .tambah-jadwal-icon svg {
      width: 16px;
      height: 16px;
      fill: #FFFFFF;
    }

    /* Card List Frame */
    .cards-wrapper {
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      padding: 0px;
      gap: 12px;
      width: 342px;
      flex: none;
      order: 3;
      align-self: stretch;
    }

    /* Schedule Card */
    .card-item {
      display: flex;
      flex-direction: row;
      align-items: center;
      padding: 14px;
      gap: 12px;
      width: 342px;
      height: 62px;
      background: #FFFFFF;
      box-shadow: 0px 2px 8px rgba(27, 42, 74, 0.0392157);
      border-radius: 12px;
      flex: none;
      align-self: stretch;
    }

    .card-info-frame {
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      padding: 0px;
      gap: 4px;
      width: 210px;
      height: 34px;
      flex: none;
      flex-grow: 1;
    }

    .staff-name {
      width: 210px;
      height: 17px;
      font-style: normal;
      font-weight: 700;
      font-size: 14px;
      line-height: 17px;
      color: #1B2A4A;
    }

    .staff-schedule {
      width: 210px;
      height: 13px;
      font-style: normal;
      font-weight: 400;
      font-size: 11px;
      line-height: 13px;
      color: #4A5568;
    }

    .action-buttons-frame {
      display: flex;
      flex-direction: row;
      align-items: flex-start;
      padding: 0px;
      gap: 4px;
      width: 92px;
      height: 28px;
      flex: none;
    }

    .btn-action {
      display: flex;
      justify-content: center;
      align-items: center;
      width: 28px;
      height: 28px;
      border-radius: 8px;
      border: none;
      cursor: pointer;
    }

    .btn-view {
      background: #E2E8F0;
    }
    .btn-view svg {
      width: 14px;
      height: 14px;
      fill: #1B2A4A;
    }

    .btn-edit {
      background: #E0F2FE;
    }
    .btn-edit svg {
      width: 16px;
      height: 16px;
      stroke: #0369A1;
    }

    .btn-delete {
      background: #FFE4E6;
    }
    .btn-delete svg {
      width: 16px;
      height: 16px;
      stroke: #B91C1C;
    }

    /* home-indicator */
    .home-indicator {
      display: flex;
      flex-direction: row;
      justify-content: center;
      align-items: flex-start;
      padding: 21px 0px 8px;
      width: 390px;
      height: 34px;
      flex: none;
      order: 1;
      align-self: stretch;
    }

    .indicator-bar {
      width: 139px;
      height: 5px;
      background: #1B2A4A;
      border-radius: 100px;
    }
  </style>
</head>
<body>

  <!-- daftar-jadwal -->
  <div class="daftar-jadwal">
    
    <!-- Frame Utama -->
    <div class="frame-main">

      <!-- screen-header -->
      <div class="screen-header">
        <div class="header-text-frame">
          <h1 class="header-title">Daftar Jadwal Piket</h1>
          <p class="header-subtitle">Kelola jadwal piket yang tersedia</p>
        </div>
        <button class="back-btn" aria-label="Kembali">
          <svg viewBox="0 0 24 24">
            <path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/>
          </svg>
        </button>
      </div>

      <!-- Frame Konten -->
      <div class="content-frame">
        
        <!-- Search Frame -->
        <div class="search-frame">
          <div class="icon-search">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="2">
              <circle cx="11" cy="11" r="8"></circle>
              <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
          </div>
          <input type="text" class="search-input" placeholder="Cari nama staff...">
        </div>

        <!-- Day Filter Tabs -->
        <div class="day-tabs-frame">
          <button class="tab-item">Senin</button>
          <button class="tab-item">Selasa</button>
          <button class="tab-item active">Rabu</button>
          <button class="tab-item">Kamis</button>
          <button class="tab-item">Jumat</button>
        </div>

        <!-- Tambah Jadwal Piket Button -->
        <button class="tambah-jadwal-frame">
          <span class="tambah-jadwal-text">Tambah Jadwal Piket</span>
          <div class="tambah-jadwal-icon">
            <svg viewBox="0 0 24 24">
              <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
            </svg>
          </div>
        </button>

        <!-- Cards List -->
        <div class="cards-wrapper">
          
          <!-- Card 1 -->
          <div class="card-item">
            <div class="card-info-frame">
              <div class="staff-name">Budi Santoso, S.Pd</div>
              <div class="staff-schedule">Rabu | 07:00 - 12:00</div>
            </div>
            <div class="action-buttons-frame">
              <button class="btn-action btn-view">
                <svg viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
              </button>
              <button class="btn-action btn-edit">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
              </button>
              <button class="btn-action btn-delete">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
              </button>
            </div>
          </div>

          <!-- Card 2 -->
          <div class="card-item">
            <div class="card-info-frame">
              <div class="staff-name">Winartin, S.Pd</div>
              <div class="staff-schedule">Rabu | 07:00 - 12:00</div>
            </div>
            <div class="action-buttons-frame">
              <button class="btn-action btn-view">
                <svg viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
              </button>
              <button class="btn-action btn-edit">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
              </button>
              <button class="btn-action btn-delete">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
              </button>
            </div>
          </div>

          <!-- Card 3 -->
          <div class="card-item">
            <div class="card-info-frame">
              <div class="staff-name">Drs. M. Yusuf</div>
              <div class="staff-schedule">Rabu | 12:00 - 16:00</div>
            </div>
            <div class="action-buttons-frame">
              <button class="btn-action btn-view">
                <svg viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
              </button>
              <button class="btn-action btn-edit">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
              </button>
              <button class="btn-action btn-delete">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
              </button>
            </div>
          </div>

          <!-- Card 4 -->
          <div class="card-item">
            <div class="card-info-frame">
              <div class="staff-name">Sarah Amelia, M.Pd</div>
              <div class="staff-schedule">Rabu | 07:00 - 12:00</div>
            </div>
            <div class="action-buttons-frame">
              <button class="btn-action btn-view">
                <svg viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
              </button>
              <button class="btn-action btn-edit">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
              </button>
              <button class="btn-action btn-delete">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
              </button>
            </div>
          </div>

        </div>
      </div>

    </div>

    <!-- home-indicator -->
    <div class="home-indicator">
      <div class="indicator-bar"></div>
    </div>

  </div>

</body>
</html>
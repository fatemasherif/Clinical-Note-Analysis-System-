<?php
require_once __DIR__ . '/BaseTemplate.php';

class AdminNotes extends BaseTemplate {
    private $notes;

    public function __construct($notes = []) {
        $this->notes = $notes;
        $content = $this->buildContent();
        parent::__construct('Manage Clinical Notes', $content);
    }

    private function buildContent() {
        $notesJson = htmlspecialchars(json_encode($this->notes), ENT_QUOTES, 'UTF-8');
        
        return <<<HTML
<div style="max-width: 1400px; margin: 0 auto; padding: 20px;">
  <h2 style="font-size: 1.875rem; color: #1e3a8a; font-weight: bold; margin-bottom: 24px;">Manage Clinical Notes</h2>

  <style>
    /* Search box */
    .filter-box {
      background-color: #fff;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      border-radius: 8px;
      padding: 24px;
      margin-bottom: 32px;
      display: flex;
      flex-wrap: wrap;
      gap: 16px;
      justify-content: space-between;
      align-items: center;
    }

    .filter-box input,
    .filter-box select {
      padding: 15px;
      width: 300px;
      border: 1px solid #d1d5db;
      border-radius: 8px;
      font-size: 1rem;
      outline: none;
      transition: box-shadow 0.2s;
    }

    .filter-box input:focus,
    .filter-box select:focus {
      box-shadow: 0 0 0 2px #3b82f6;
    }

    /* Table Section */
    section {
      background-color: #fff;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      border-radius: 8px;
      padding: 32px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    th, td {
      text-align: left;
      padding: 12px 16px;
      border-bottom: 1px solid #e5e7eb;
    }

    th {
      background-color: #eff6ff;
      color: #1e40af;
      font-weight: bold;
    }

    tr:hover {
      background-color: #f9fafb;
    }

    .status-approved {
      color: #16a34a;
      font-weight: 600;
      text-transform: capitalize;
    }

    .status-pending {
      color: #ca8a04;
      font-weight: 600;
      text-transform: capitalize;
    }

    .status-rejected {
      color: #dc2626;
      font-weight: 600;
      text-transform: capitalize;
    }

    /* Buttons */
    button {
      border: none;
      padding: 8px 12px;
      border-radius: 6px;
      color: #fff;
      font-size: 0.9rem;
      cursor: pointer;
      margin: 0 4px;
      transition: background-color 0.2s;
    }

    .btn-approve {
      background-color: #22c55e;
    }
    .btn-approve:hover {
      background-color: #16a34a;
    }

    .btn-reject {
      background-color: #ef4444;
    }
    .btn-reject:hover {
      background-color: #dc2626;
    }

    .btn-delete {
      background-color: #9ca3af;
    }
    .btn-delete:hover {
      background-color: #6b7280;
    }

    /* Responsive */
    @media (max-width: 768px) {
      .filter-box {
        flex-direction: column;
        align-items: stretch;
      }

      .filter-box input,
      .filter-box select {
        width: 100%;
      }
    }
  </style>

  <!-- Search -->
  <div class="filter-box">
    <input id="searchInput" type="text" placeholder="Search by Doctor or Diagnosis...">
    <select id="filterSelect">
      <option value="">All Status</option>
      <option value="pending">Pending</option>
      <option value="approved">Approved</option>
      <option value="rejected">Rejected</option>
    </select>
  </div>

  <!-- Table -->
  <section>
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Doctor</th>
          <th>Diagnosis</th>
          <th>Summary</th>
          <th>Date</th>
          <th>Status</th>
          <th style="text-align: center;">Actions</th>
        </tr>
      </thead>
      <tbody id="notesTable"></tbody>
    </table>
  </section>

  <!-- JavaScript -->
  <script>
    const notes = {$notesJson};

    function renderTable(data) {
      const tableBody = document.getElementById("notesTable");
      tableBody.innerHTML = "";

      if (data.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 20px; color: #6b7280;">No clinical notes found.</td></tr>';
        return;
      }

      data.forEach(function(note) {
        const row = '<tr>' +
          '<td>' + note.id + '</td>' +
          '<td>' + note.doctor + '</td>' +
          '<td>' + note.diagnosis + '</td>' +
          '<td>' + note.summary + '</td>' +
          '<td>' + note.date + '</td>' +
          '<td class="status-' + note.status + '">' + note.status + '</td>' +
          '<td style="text-align: center;">' +
          '<button class="btn-approve" onclick="updateStatus(' + note.id + ', \'approved\')">Approve</button>' +
          '<button class="btn-reject" onclick="updateStatus(' + note.id + ', \'rejected\')">Reject</button>' +
          '<button class="btn-delete" onclick="deleteNote(' + note.id + ')">Delete</button>' +
          '</td>' +
          '</tr>';
        tableBody.innerHTML += row;
      });
    }

    function updateStatus(id, status) {
      const note = notes.find(n => n.id === id);
      if (note) note.status = status;
      renderTable(notes);
    }

    function deleteNote(id) {
      if (confirm('Are you sure you want to delete this note?')) {
        const index = notes.findIndex(n => n.id === id);
        if (index !== -1) {
          notes.splice(index, 1);
          renderTable(notes);
        }
      }
    }

    document.getElementById("searchInput").addEventListener("input", (e) => {
      const term = e.target.value.toLowerCase();
      const filtered = notes.filter(n =>
        n.doctor.toLowerCase().includes(term) || n.diagnosis.toLowerCase().includes(term)
      );
      renderTable(filtered);
    });

    document.getElementById("filterSelect").addEventListener("change", (e) => {
      const status = e.target.value;
      const filtered = status ? notes.filter(n => n.status === status) : notes;
      renderTable(filtered);
    });

    // Initial render
    renderTable(notes);
  </script>
</div>
HTML;
    }
}
?>

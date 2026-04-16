/**
 * Gestión de Trámites — integración con GET/POST/PUT/DELETE /api/tramites
 * Tabla: tramites (+ workspace_id, folio en migración 2026_04_15_121000)
 *
 * Copia a tu src (ajusta imports: ./Sidebar o ../components/Sidebar).
 */

import React, { useState, useMemo, Fragment, useEffect, useCallback } from 'react';
import Sidebar from './Sidebar';
import Navbar from './Navbar';
import {
  FiTrash2,
  FiSearch,
  FiPlus,
  FiAlertCircle,
  FiCalendar,
  FiClock,
  FiCheckCircle,
  FiXCircle,
  FiChevronLeft,
  FiChevronRight,
  FiFilter,
  FiDownload,
  FiUser,
  FiFileText,
  FiX,
  FiMoreVertical,
  FiEye,
  FiCopy,
  FiPrinter,
  FiEdit2,
} from 'react-icons/fi';
import { Menu, Transition } from '@headlessui/react';
import { motion, AnimatePresence } from 'framer-motion';
import './css/Tramites.css';

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';

function authHeaders() {
  const token = localStorage.getItem('token');
  return {
    'Content-Type': 'application/json',
    Accept: 'application/json',
    ...(token ? { Authorization: `Bearer ${token}` } : {}),
  };
}

function mapTramiteFromApi(row) {
  const sem = (row.semaforo || 'verde').toLowerCase();
  const status = ['verde', 'amarillo', 'rojo'].includes(sem) ? sem : 'verde';
  return {
    id: row.id,
    raw: row,
    folio: row.folio || row.id,
    etapa: row.etapa || '—',
    agente: row.nombre_agente || '—',
    ramo: row.ramo || '—',
    poliza: row.poliza_referencia || '—',
    fecha: row.fecha_alta?.slice?.(0, 10) || '',
    ultimaModificacion: row.fecha_ultima_modificacion?.slice?.(0, 10) || row.updated_at?.slice?.(0, 10) || '',
    status,
  };
}

const Tramites = () => {
  const [isSidebarExpanded, setIsSidebarExpanded] = useState(false);
  const [searchTerm, setSearchTerm] = useState('');
  const [isDeleteModalOpen, setIsDeleteModalOpen] = useState(false);
  const [isAddModalOpen, setIsAddModalOpen] = useState(false);
  const [isEditModalOpen, setIsEditModalOpen] = useState(false);
  const [selectedItem, setSelectedItem] = useState(null);
  const [currentPage, setCurrentPage] = useState(1);
  const [filterStatus, setFilterStatus] = useState('todos');
  const itemsPerPage = 5;

  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(true);
  const [listError, setListError] = useState('');

  const loadTramites = useCallback(async () => {
    setLoading(true);
    setListError('');
    try {
      const res = await fetch(`${API_URL}/tramites?per_page=200`, { headers: authHeaders() });
      const json = await res.json();
      if (!res.ok) throw new Error(json.message || 'Error al cargar trámites');
      const rows = json.data || [];
      setData(rows.map(mapTramiteFromApi));
    } catch (e) {
      setListError(e.message || 'Error de red');
      setData([]);
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    loadTramites();
  }, [loadTramites]);

  const [newTramite, setNewTramite] = useState({
    folio: '',
    etapa: 'Cotización',
    agente: '',
    ramo: '',
    poliza: '',
    fecha: '',
    status: 'verde',
  });

  const [editTramite, setEditTramite] = useState({
    id: null,
    folio: '',
    etapa: '',
    agente: '',
    ramo: '',
    poliza: '',
    fecha: '',
    status: '',
  });

  const getStatusConfig = (status) => {
    switch (status) {
      case 'verde':
        return {
          color: 'tramites-bg-green-500',
          text: '¡Excelente! Estás a tiempo con este trámite.',
          bg: 'tramites-bg-green-50',
          border: 'tramites-border-green-200',
          icon: <FiCheckCircle className="tramites-text-green-500" />,
        };
      case 'amarillo':
        return {
          color: 'tramites-bg-yellow-400',
          text: 'Atención: El trámite está por vencer, revisa los detalles.',
          bg: 'tramites-bg-yellow-50',
          border: 'tramites-border-yellow-200',
          icon: <FiClock className="tramites-text-yellow-500" />,
        };
      case 'rojo':
        return {
          color: 'tramites-bg-red-500',
          text: 'Urgente: El trámite ya pasó su tiempo de atención, requiere prioridad inmediata.',
          bg: 'tramites-bg-red-50',
          border: 'tramites-border-red-200',
          icon: <FiXCircle className="tramites-text-red-500" />,
        };
      default:
        return {
          color: 'tramites-bg-gray-300',
          text: '',
          bg: 'tramites-bg-gray-50',
          border: 'tramites-border-gray-200',
          icon: null,
        };
    }
  };

  const handleAddTramite = async (e) => {
    e.preventDefault();
    try {
      const res = await fetch(`${API_URL}/tramites`, {
        method: 'POST',
        headers: authHeaders(),
        body: JSON.stringify({
          folio: newTramite.folio || null,
          etapa: newTramite.etapa,
          nombre_agente: newTramite.agente,
          ramo: newTramite.ramo,
          poliza_referencia: newTramite.poliza || null,
          fecha_alta: newTramite.fecha || null,
          semaforo: newTramite.status,
        }),
      });
      const json = await res.json();
      if (!res.ok) throw new Error(json.message || JSON.stringify(json.errors || {}));
      setIsAddModalOpen(false);
      setNewTramite({
        folio: '',
        etapa: 'Cotización',
        agente: '',
        ramo: '',
        poliza: '',
        fecha: '',
        status: 'verde',
      });
      loadTramites();
    } catch (err) {
      alert(err.message || 'No se pudo guardar');
    }
  };

  const handleEditClick = (item) => {
    setSelectedItem(item);
    setEditTramite({
      id: item.id,
      folio: String(item.folio ?? ''),
      etapa: item.etapa,
      agente: item.agente,
      ramo: item.ramo,
      poliza: item.poliza === '—' ? '' : item.poliza,
      fecha: item.fecha,
      status: item.status,
    });
    setIsEditModalOpen(true);
  };

  const handleSaveEdit = async (e) => {
    e.preventDefault();
    try {
      const res = await fetch(`${API_URL}/tramites/${editTramite.id}`, {
        method: 'PUT',
        headers: authHeaders(),
        body: JSON.stringify({
          folio: editTramite.folio || null,
          etapa: editTramite.etapa,
          nombre_agente: editTramite.agente,
          ramo: editTramite.ramo,
          poliza_referencia: editTramite.poliza || null,
          fecha_alta: editTramite.fecha || null,
          semaforo: editTramite.status,
        }),
      });
      const json = await res.json();
      if (!res.ok) throw new Error(json.message || 'Error al actualizar');
      setIsEditModalOpen(false);
      setSelectedItem(null);
      loadTramites();
    } catch (err) {
      alert(err.message || 'No se pudo actualizar');
    }
  };

  const handleDeleteConfirm = async () => {
    if (!selectedItem?.id) return;
    try {
      const res = await fetch(`${API_URL}/tramites/${selectedItem.id}`, {
        method: 'DELETE',
        headers: authHeaders(),
      });
      if (!res.ok) {
        const json = await res.json().catch(() => ({}));
        throw new Error(json.message || `Error ${res.status}`);
      }
      setIsDeleteModalOpen(false);
      setSelectedItem(null);
      loadTramites();
    } catch (err) {
      alert(err.message || 'No se pudo eliminar');
    }
  };

  const filteredData = useMemo(() => {
    let filtered = data.filter((item) => {
      const matchesSearch =
        searchTerm === '' ||
        String(item.folio).toLowerCase().includes(searchTerm.toLowerCase()) ||
        item.agente.toLowerCase().includes(searchTerm.toLowerCase()) ||
        item.ramo.toLowerCase().includes(searchTerm.toLowerCase()) ||
        item.etapa.toLowerCase().includes(searchTerm.toLowerCase());
      const matchesStatus = filterStatus === 'todos' || item.status === filterStatus;
      return matchesSearch && matchesStatus;
    });
    return filtered.sort((a, b) => new Date(b.ultimaModificacion || 0) - new Date(a.ultimaModificacion || 0));
  }, [searchTerm, data, filterStatus]);

  const totalPages = Math.max(1, Math.ceil(filteredData.length / itemsPerPage));
  const paginatedData = filteredData.slice(
    (currentPage - 1) * itemsPerPage,
    currentPage * itemsPerPage
  );

  useEffect(() => {
    setCurrentPage((p) => Math.min(p, totalPages));
  }, [totalPages]);

  return (
    <div className="tramites-container">
      <Sidebar onExpand={setIsSidebarExpanded} />

      <div
        className={`tramites-main-content ${
          isSidebarExpanded ? 'tramites-sidebar-expanded' : 'tramites-sidebar-collapsed'
        }`}
      >
        <Navbar />

        <main className="tramites-main">
          <div className="tramites-page-header">
            <div>
              <h1 className="tramites-page-title">Gestión de Trámites</h1>
              <p className="tramites-page-subtitle">Control y seguimiento (API /api/tramites)</p>
            </div>

            <div className="tramites-status-indicators">
              {['verde', 'amarillo', 'rojo'].map((status) => {
                const count = data.filter((item) => item.status === status).length;
                const config = getStatusConfig(status);
                return (
                  <div key={status} className="tramites-status-indicator-container">
                    <div className="tramites-status-indicator">
                      <div className={`tramites-indicator-dot ${config.color}`} />
                      <span className="tramites-indicator-label">{status}</span>
                      <span className="tramites-indicator-count">{count}</span>
                    </div>
                    <div className="tramites-status-tooltip">
                      <div className={`tramites-tooltip-content ${status}`}>
                        <div className="tramites-tooltip-header">
                          <div className={`tramites-tooltip-dot ${config.color}`} />
                          <span className="tramites-tooltip-title">
                            {status === 'verde' && 'Normal'}
                            {status === 'amarillo' && 'Precaución'}
                            {status === 'rojo' && 'Urgente'}
                          </span>
                        </div>
                        <p className="tramites-tooltip-text">{config.text}</p>
                        <div className="tramites-tooltip-stats">
                          <span className="tramites-tooltip-count">{count} trámites</span>
                          <span className="tramites-tooltip-percentage">
                            {data.length ? Math.round((count / data.length) * 100) : 0}%
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>
                );
              })}
            </div>
          </div>

          {listError && <p className="gsea-auth-error" style={{ margin: '0 1rem 1rem' }}>{listError}</p>}
          {loading && <p style={{ margin: '0 1rem 1rem' }}>Cargando…</p>}

          <div className="tramites-tools-bar">
            <div className="tramites-search-wrapper">
              <FiSearch className="tramites-search-icon" />
              <input
                type="text"
                placeholder="Buscar por folio, agente o ramo..."
                className="tramites-search-input"
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
              />
            </div>

            <div className="tramites-tools-wrapper">
              <div className="tramites-filter-group">
                <FiFilter className="tramites-filter-icon" />
                <select
                  value={filterStatus}
                  onChange={(e) => setFilterStatus(e.target.value)}
                  className="tramites-filter-select"
                >
                  <option value="todos">Todos los estados</option>
                  <option value="verde">Verde</option>
                  <option value="amarillo">Amarillo</option>
                  <option value="rojo">Rojo</option>
                </select>
              </div>

              <button type="button" className="tramites-btn-export" disabled title="Pendiente exportar">
                <FiDownload />
                <span>Exportar</span>
              </button>

              <button type="button" className="tramites-btn-add" onClick={() => setIsAddModalOpen(true)}>
                <FiPlus />
                <span>Nuevo Trámite</span>
              </button>
            </div>
          </div>

          <div className="tramites-table-container">
            <div className="tramites-table-responsive">
              <table className="tramites-table">
                <thead>
                  <tr>
                    <th>Folio</th>
                    <th>Etapa</th>
                    <th>Agente</th>
                    <th>Ramo</th>
                    <th>Póliza</th>
                    <th>Estado</th>
                    <th>Última Modificación</th>
                    <th>Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  {paginatedData.map((item) => {
                    const statusConfig = getStatusConfig(item.status);
                    return (
                      <motion.tr
                        key={item.id}
                        initial={{ opacity: 0, y: 10 }}
                        animate={{ opacity: 1, y: 0 }}
                        exit={{ opacity: 0, y: -10 }}
                        className="tramites-table-row"
                      >
                        <td className="tramites-folio-cell">
                          <span className="tramites-folio-badge">{item.folio}</span>
                        </td>
                        <td>
                          <span className="tramites-etapa-badge">{item.etapa}</span>
                        </td>
                        <td className="tramites-agent-cell">
                          <div className="tramites-agent-info">
                            <FiUser className="tramites-agent-icon" />
                            <span>{item.agente}</span>
                          </div>
                        </td>
                        <td>{item.ramo}</td>
                        <td>
                          <span className="tramites-poliza-text">{item.poliza}</span>
                        </td>
                        <td>
                          <div className="tramites-status-container">
                            <div className={`tramites-status-dot ${statusConfig.color}`} />
                            <span className={`tramites-status-text tramites-${item.status}`}>{item.status}</span>
                          </div>
                        </td>
                        <td>
                          <div className="tramites-date-cell">
                            <FiCalendar className="tramites-date-icon" />
                            <span>
                              {item.ultimaModificacion
                                ? new Date(item.ultimaModificacion).toLocaleDateString('es-MX')
                                : '—'}
                            </span>
                          </div>
                        </td>
                        <td>
                          <div className="tramites-actions-cell">
                            <motion.button
                              whileHover={{ scale: 1.1 }}
                              whileTap={{ scale: 0.95 }}
                              type="button"
                              className="tramites-action-btn tramites-edit-btn"
                              onClick={() => handleEditClick(item)}
                              title="Editar"
                            >
                              <FiEdit2 />
                            </motion.button>
                            <motion.button
                              whileHover={{ scale: 1.1 }}
                              whileTap={{ scale: 0.95 }}
                              type="button"
                              className="tramites-action-btn tramites-delete-btn"
                              onClick={() => {
                                setSelectedItem(item);
                                setIsDeleteModalOpen(true);
                              }}
                              title="Eliminar"
                            >
                              <FiTrash2 />
                            </motion.button>
                            <Menu as="div" className="tramites-actions-menu">
                              <Menu.Button as="div" className="tramites-actions-button" title="Más opciones">
                                <FiMoreVertical size={18} />
                              </Menu.Button>
                              <Transition
                                as={Fragment}
                                enter="tramites-transition-enter"
                                enterFrom="tramites-transition-enter-from"
                                enterTo="tramites-transition-enter-to"
                                leave="tramites-transition-leave"
                                leaveFrom="tramites-transition-leave-from"
                                leaveTo="tramites-transition-leave-to"
                              >
                                <Menu.Items className="tramites-menu-items">
                                  <Menu.Item>
                                    {({ active }) => (
                                      <button
                                        type="button"
                                        className={`tramites-menu-item ${active ? 'tramites-menu-item-active' : ''}`}
                                      >
                                        <FiEye size={14} />
                                        <span>Ver detalles</span>
                                      </button>
                                    )}
                                  </Menu.Item>
                                  <Menu.Item>
                                    {({ active }) => (
                                      <button
                                        type="button"
                                        className={`tramites-menu-item ${active ? 'tramites-menu-item-active' : ''}`}
                                      >
                                        <FiCopy size={14} />
                                        <span>Duplicar</span>
                                      </button>
                                    )}
                                  </Menu.Item>
                                  <Menu.Item>
                                    {({ active }) => (
                                      <button
                                        type="button"
                                        className={`tramites-menu-item ${active ? 'tramites-menu-item-active' : ''}`}
                                      >
                                        <FiPrinter size={14} />
                                        <span>Imprimir</span>
                                      </button>
                                    )}
                                  </Menu.Item>
                                </Menu.Items>
                              </Transition>
                            </Menu>
                          </div>
                        </td>
                      </motion.tr>
                    );
                  })}
                </tbody>
              </table>
            </div>

            <div className="tramites-pagination">
              <div className="tramites-pagination-info">
                Mostrando {paginatedData.length} de {filteredData.length} registros
              </div>
              <div className="tramites-pagination-controls">
                <motion.button
                  whileHover={{ scale: 1.05 }}
                  whileTap={{ scale: 0.95 }}
                  type="button"
                  className="tramites-pagination-btn"
                  onClick={() => setCurrentPage((prev) => Math.max(1, prev - 1))}
                  disabled={currentPage === 1}
                >
                  <FiChevronLeft />
                </motion.button>
                <span className="tramites-pagination-page">
                  Página {currentPage} de {totalPages}
                </span>
                <motion.button
                  whileHover={{ scale: 1.05 }}
                  whileTap={{ scale: 0.95 }}
                  type="button"
                  className="tramites-pagination-btn"
                  onClick={() => setCurrentPage((prev) => Math.min(totalPages, prev + 1))}
                  disabled={currentPage === totalPages}
                >
                  <FiChevronRight />
                </motion.button>
              </div>
            </div>
          </div>
        </main>
      </div>

      <AnimatePresence>
        {isAddModalOpen && (
          <div className="tramites-modal-overlay">
            <motion.div
              initial={{ scale: 0.9, opacity: 0, y: 20 }}
              animate={{ scale: 1, opacity: 1, y: 0 }}
              exit={{ scale: 0.9, opacity: 0, y: 20 }}
              className="tramites-modal-panel tramites-modal-add"
            >
              <div className="tramites-modal-header">
                <div className="tramites-modal-header-icon">
                  <FiFileText />
                </div>
                <h2 className="tramites-modal-title">Nuevo Trámite</h2>
                <button type="button" className="tramites-modal-close" onClick={() => setIsAddModalOpen(false)}>
                  <FiX size={20} />
                </button>
              </div>
              <form onSubmit={handleAddTramite}>
                <div className="tramites-modal-form">
                  <div className="tramites-form-grid">
                    <div className="tramites-form-group">
                      <label>Folio</label>
                      <input
                        type="text"
                        placeholder="Ej: 1091754"
                        value={newTramite.folio}
                        onChange={(e) => setNewTramite({ ...newTramite, folio: e.target.value })}
                      />
                    </div>
                    <div className="tramites-form-group">
                      <label>Etapa *</label>
                      <select
                        value={newTramite.etapa}
                        onChange={(e) => setNewTramite({ ...newTramite, etapa: e.target.value })}
                        required
                      >
                        <option>Cotización</option>
                        <option>Emisión</option>
                        <option>Cobranza</option>
                        <option>Revisión</option>
                        <option>Aprobación</option>
                      </select>
                    </div>
                    <div className="tramites-form-group">
                      <label>Agente *</label>
                      <input
                        type="text"
                        placeholder="Nombre del agente"
                        value={newTramite.agente}
                        onChange={(e) => setNewTramite({ ...newTramite, agente: e.target.value })}
                        required
                      />
                    </div>
                    <div className="tramites-form-group">
                      <label>Ramo *</label>
                      <input
                        type="text"
                        placeholder="Ej: Autos, Vida, Salud"
                        value={newTramite.ramo}
                        onChange={(e) => setNewTramite({ ...newTramite, ramo: e.target.value })}
                        required
                      />
                    </div>
                    <div className="tramites-form-group">
                      <label>Póliza (ref.)</label>
                      <input
                        type="text"
                        placeholder="Número de póliza"
                        value={newTramite.poliza}
                        onChange={(e) => setNewTramite({ ...newTramite, poliza: e.target.value })}
                      />
                    </div>
                    <div className="tramites-form-group">
                      <label>Fecha alta *</label>
                      <input
                        type="date"
                        value={newTramite.fecha}
                        onChange={(e) => setNewTramite({ ...newTramite, fecha: e.target.value })}
                        required
                      />
                    </div>
                    <div className="tramites-form-group tramites-full-width">
                      <label>Semáforo *</label>
                      <select
                        value={newTramite.status}
                        onChange={(e) => setNewTramite({ ...newTramite, status: e.target.value })}
                        required
                      >
                        <option value="verde">Verde - Normal</option>
                        <option value="amarillo">Amarillo - Por vencer</option>
                        <option value="rojo">Rojo - Urgente</option>
                      </select>
                    </div>
                  </div>
                </div>
                <div className="tramites-modal-footer">
                  <motion.button
                    whileHover={{ scale: 1.02 }}
                    whileTap={{ scale: 0.98 }}
                    type="button"
                    className="tramites-btn-cancel"
                    onClick={() => setIsAddModalOpen(false)}
                  >
                    Cancelar
                  </motion.button>
                  <motion.button whileHover={{ scale: 1.02 }} whileTap={{ scale: 0.98 }} type="submit" className="tramites-btn-save">
                    Guardar Trámite
                  </motion.button>
                </div>
              </form>
            </motion.div>
          </div>
        )}
      </AnimatePresence>

      <AnimatePresence>
        {isEditModalOpen && (
          <div className="tramites-modal-overlay">
            <motion.div
              initial={{ scale: 0.9, opacity: 0, y: 20 }}
              animate={{ scale: 1, opacity: 1, y: 0 }}
              exit={{ scale: 0.9, opacity: 0, y: 20 }}
              className="tramites-modal-panel tramites-modal-add"
            >
              <div className="tramites-modal-header">
                <h2 className="tramites-modal-title">Editar Trámite</h2>
                <button
                  type="button"
                  className="tramites-modal-close"
                  onClick={() => {
                    setIsEditModalOpen(false);
                    setSelectedItem(null);
                  }}
                >
                  <FiX size={20} />
                </button>
              </div>
              <form onSubmit={handleSaveEdit}>
                <div className="tramites-modal-form">
                  <div className="tramites-form-grid">
                    <div className="tramites-form-group">
                      <label>Folio</label>
                      <input
                        type="text"
                        value={editTramite.folio}
                        onChange={(e) => setEditTramite({ ...editTramite, folio: e.target.value })}
                      />
                    </div>
                    <div className="tramites-form-group">
                      <label>Etapa *</label>
                      <select
                        value={editTramite.etapa}
                        onChange={(e) => setEditTramite({ ...editTramite, etapa: e.target.value })}
                        required
                      >
                        <option>Cotización</option>
                        <option>Emisión</option>
                        <option>Cobranza</option>
                        <option>Revisión</option>
                        <option>Aprobación</option>
                      </select>
                    </div>
                    <div className="tramites-form-group">
                      <label>Agente *</label>
                      <input
                        type="text"
                        value={editTramite.agente}
                        onChange={(e) => setEditTramite({ ...editTramite, agente: e.target.value })}
                        required
                      />
                    </div>
                    <div className="tramites-form-group">
                      <label>Ramo *</label>
                      <input
                        type="text"
                        value={editTramite.ramo}
                        onChange={(e) => setEditTramite({ ...editTramite, ramo: e.target.value })}
                        required
                      />
                    </div>
                    <div className="tramites-form-group">
                      <label>Póliza (ref.)</label>
                      <input
                        type="text"
                        value={editTramite.poliza}
                        onChange={(e) => setEditTramite({ ...editTramite, poliza: e.target.value })}
                      />
                    </div>
                    <div className="tramites-form-group">
                      <label>Fecha alta *</label>
                      <input
                        type="date"
                        value={editTramite.fecha}
                        onChange={(e) => setEditTramite({ ...editTramite, fecha: e.target.value })}
                        required
                      />
                    </div>
                    <div className="tramites-form-group tramites-full-width">
                      <label>Semáforo *</label>
                      <select
                        value={editTramite.status}
                        onChange={(e) => setEditTramite({ ...editTramite, status: e.target.value })}
                        required
                      >
                        <option value="verde">Verde - Normal</option>
                        <option value="amarillo">Amarillo - Por vencer</option>
                        <option value="rojo">Rojo - Urgente</option>
                      </select>
                    </div>
                  </div>
                </div>
                <div className="tramites-modal-footer">
                  <motion.button
                    whileHover={{ scale: 1.02 }}
                    whileTap={{ scale: 0.98 }}
                    type="button"
                    className="tramites-btn-cancel"
                    onClick={() => {
                      setIsEditModalOpen(false);
                      setSelectedItem(null);
                    }}
                  >
                    Cancelar
                  </motion.button>
                  <motion.button whileHover={{ scale: 1.02 }} whileTap={{ scale: 0.98 }} type="submit" className="tramites-btn-save">
                    Guardar Cambios
                  </motion.button>
                </div>
              </form>
            </motion.div>
          </div>
        )}
      </AnimatePresence>

      <AnimatePresence>
        {isDeleteModalOpen && (
          <div className="tramites-modal-overlay tramites-modal-overlay-delete">
            <motion.div
              initial={{ scale: 0.9, opacity: 0 }}
              animate={{ scale: 1, opacity: 1 }}
              exit={{ scale: 0.9, opacity: 0 }}
              className="tramites-modal-panel tramites-modal-delete"
            >
              <div className="tramites-delete-icon-container">
                <FiAlertCircle className="tramites-delete-icon" />
              </div>
              <h3 className="tramites-delete-title">¿Eliminar Trámite?</h3>
              <p className="tramites-delete-text">
                Vas a eliminar el folio <strong>#{selectedItem?.folio}</strong> de <strong>{selectedItem?.agente}</strong>. Esta acción no se puede deshacer.
              </p>
              <div className="tramites-delete-actions">
                <motion.button whileHover={{ scale: 1.02 }} whileTap={{ scale: 0.98 }} type="button" onClick={handleDeleteConfirm} className="tramites-delete-confirm">
                  Eliminar Ahora
                </motion.button>
                <motion.button
                  whileHover={{ scale: 1.02 }}
                  whileTap={{ scale: 0.98 }}
                  type="button"
                  onClick={() => {
                    setIsDeleteModalOpen(false);
                    setSelectedItem(null);
                  }}
                  className="tramites-delete-cancel"
                >
                  Cancelar
                </motion.button>
              </div>
            </motion.div>
          </div>
        )}
      </AnimatePresence>
    </div>
  );
};

export default Tramites;

import request from './request';

const birthdayService = {
  getAll: (params) =>
    request.get('dashboard/admin/birthday/paginate', { params }),
  selectCategory: (params) =>
    request.get('dashboard/admin/birthday/select-paginate', { params }),
  getById: (id, params) =>
    request.get(`dashboard/admin/birthday/${id}`, { params }),
  create: (data) => request.post('dashboard/admin/birthday', data, {}),
  update: (id, data) =>
    request.put(`dashboard/admin/birthday/${id}`, data, {}),
  delete: (id) => request.delete(`dashboard/admin/birthday`, { data: id }),
  search: (params) =>
    request.get('dashboard/admin/birthday/search', { params }),
  setActive: (id) => request.post(`dashboard/admin/birthday/active/${id}`),
  export: () => request.get(`dashboard/admin/birthday/export`),
  import: (data) => request.post('dashboard/admin/birthday/import', data, {}),
};

export default birthdayService;

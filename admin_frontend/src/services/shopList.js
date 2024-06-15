import request from './request';

const shopListService = {
    getAll: () => request.get('dashboard/user/shopList', {}),
    getById: (id, params) =>
      request.get(`dashboard/user/showShopList/${id}`, { params }),
   // create: (data) => request.post('dashboard/admin/event', data),
   // update: (id, params) => request.put(`dashboard/admin/event/${id}`, {}, { params }),
  //  delete: (id) => request.delete(`dashboard/admin/event/${id}`),
};

export default shopListService;

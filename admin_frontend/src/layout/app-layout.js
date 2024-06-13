import React, { Suspense, useEffect } from 'react';
import { Outlet } from 'react-router-dom';
import { shallowEqual, useDispatch, useSelector } from 'react-redux';
import { Layout } from 'antd';
import Sidebar from '../components/sidebar';
import TabMenu from '../components/tab-menu';
import ChatIcons from '../views/chat/chat-icons';
import Footer from '../components/footer';
import languagesService from '../services/languages';
import { setLangugages } from '../redux/slices/formLang';
import { fetchAllShops } from '../redux/slices/allShops';
import { fetchCurrencies, fetchRestCurrencies } from '../redux/slices/currency';
import { data } from '../configs/menu-config-test';
import { setUserData } from '../redux/slices/auth';
import { fetchMyShop } from '../redux/slices/myShop';
import Loading from 'components/loading';
import ParcelFloat from 'views/parcel-order/parcel-float';
const { Content } = Layout;

const AppLayout = () => {
  const dispatch = useDispatch();
  const { languages } = useSelector((state) => state.formLang, shallowEqual);
  const { user } = useSelector((state) => state.auth, shallowEqual);
  const { direction, navCollapsed } = useSelector(
    (state) => state.theme.theme,
    shallowEqual
  );

  const fetchLanguages = () => {
    languagesService.getAllActive().then(({ data }) => {
      dispatch(setLangugages(data));
    });
  };

  useEffect(() => {
    const params = {
      page: 1,
      perPage: 1,
    };
    if (!languages.length) {
      fetchLanguages();
    }
    if (user?.role === 'seller' || user?.role === 'moderator') {
      dispatch(fetchMyShop());
    }
    if (user?.role === 'admin' || user?.role === 'manager') {
      dispatch(fetchAllShops(params));
      dispatch(fetchCurrencies());
    } else {
      dispatch(fetchRestCurrencies());
    }
  }, []);

  useEffect(() => {
    // for development purpose only
    const userObj = {
      ...user,
      urls: data[user.role],
    };
    dispatch(setUserData(userObj));
  }, []);

  const getLayoutGutter = () => {
    // return navCollapsed ? SIDE_NAV_COLLAPSED_WIDTH : SIDE_NAV_WIDTH
    return navCollapsed ? 80 : 250;
  };

  const getLayoutDirectionGutter = () => {
    if (direction === 'ltr') {
      return { paddingLeft: getLayoutGutter(), minHeight: '100vh' };
    }
    if (direction === 'rtl') {
      return { paddingRight: getLayoutGutter(), minHeight: '100vh' };
    }
    return { paddingLeft: getLayoutGutter() };
  };

  return (
    <Layout className='app-container'>
      <Sidebar />
      <Layout className='app-layout' style={getLayoutDirectionGutter()}>
        <TabMenu />
        <Content className='p-3' style={{ flex: '1 0 70%' }}>
          <Suspense fallback={<Loading />}>
            <Outlet />
          </Suspense>
        </Content>
        <Footer />
      </Layout>
      {user?.role === 'admin' ||
      user?.role === 'seller' ||
      user?.role === 'deliveryman' ? (
        <ChatIcons />
      ) : (
        ''
      )}
      {user?.role === 'admin' && <ParcelFloat />}
    </Layout>
  );
};

export default AppLayout;

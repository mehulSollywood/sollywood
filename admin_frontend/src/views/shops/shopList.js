import React, { useEffect, useState } from 'react';
import { useDispatch } from 'react-redux';
import { Button, Space, Table } from 'antd';
import { useTranslation } from 'react-i18next';
import { toast } from 'react-toastify';
import GlobalContainer from '../../components/global-container';
import CustomModal from '../../components/modal';
import shopListService from '../../services/shopList';
import { useNavigate } from 'react-router-dom'; // Import useHistory
import { addMenu, disableRefetch } from '../../redux/slices/menu';
import { shallowEqual, useSelector } from 'react-redux';

const ShopList = () => {
  const { t } = useTranslation();
  const navigate = useNavigate(); // Initialize useHistory
  const [loadingBtn, setLoadingBtn] = useState(false);
  const [loadingData, setLoadingData] = useState(true); // State to track loading of data
  const [shopListData, setShopListData] = useState([]);
  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const dispatch = useDispatch();
  const [isModalVisible, setIsModalVisible] = useState(false); // State to control modal visibility
 
  const [columns, setColumns] = useState([
    {
      title: t('id'),
      dataIndex: 'id',
      key: 'id',
      is_show: true, // Example: assuming 'id' column is always shown
    },
    {
      title: t('user'),
      dataIndex: 'firstname',
      key: 'firstname',
      render: (firstname) => firstname || '-',
      is_show: true, // Example: assuming 'user' column is always shown
    },
    {
      title: t('options'),
      key: 'options',
      render: (_, row) => (
        <Space>
          <Button type='primary' title='shop' onClick={() => handleShopClick(row)}>
            {t('shop')}
          </Button>
        </Space>
      ),
      is_show: true, // Example: assuming 'options' column is always shown
    },
  ]);
  
  
  useEffect(() => {
    setLoadingBtn(true);
    shopListService.getAll()
      .then(response => {
        const dataArray = Object.values(response.data);
        console.log("data are",dataArray[0]);
        setShopListData(dataArray[0]);
      })
      .catch(error => {
        toast.error(t('Error fetching shop list'));
      })
      .finally(() => {
        setLoadingData(false); // Set loadingData to false when data fetching is finished
        setLoadingBtn(false);
      });
  }, []);

  const handleShopClick = (row) => {
    dispatch(
      addMenu({
        url: `showShopList/${row.id}`,
        id: 'showShopList',
        name: t('showShopList'),
      })
    );
    navigate(`/showShopList/${row.id}`);
  };

  return (
    <GlobalContainer
      headerTitle={t('Shop List')}
      columns={columns}
      setColumns={setColumns}
    >
      <Table
       columns={columns?.filter((items) => items.is_show)}
        dataSource={shopListData}
        rowKey={(record) => record.id}
        loading={loadingData} // Set loading to loadingData state
        pagination={false}
      />
      <CustomModal
        visible={isModalVisible}
        onCancel={() => setIsModalVisible(false)}
        text={t('change.default.language')}
        loading={loadingBtn}
      />
    </GlobalContainer>
  );
};

export default ShopList;

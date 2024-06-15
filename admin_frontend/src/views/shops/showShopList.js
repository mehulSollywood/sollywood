import React, { useEffect, useState } from 'react';
import { useDispatch } from 'react-redux';
import { Button, Space, Table } from 'antd';
import { useTranslation } from 'react-i18next';
import { toast } from 'react-toastify';
import GlobalContainer from '../../components/global-container';
import AddCommissionModel from '../shops/addCommissionModel';
import shopListService from '../../services/shopList';
import { useNavigate, useParams } from 'react-router-dom';

const ShowShopList = () => {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const [loadingBtn, setLoadingBtn] = useState(false);
  const [loadingData, setLoadingData] = useState(true);
  const [shopListData, setShopListData] = useState([]);
  const dispatch = useDispatch();
  const [modalVisible, setModalVisible] = useState(false);

  const { id } = useParams(); // Get ID from URL parameters

  const handleShopClick = (row) => {
    setModalVisible(true);
  };

  const handleModalCancel = () => {
    setModalVisible(false);
  };

  useEffect(() => {
    if (id) {
      setLoadingBtn(true);
      shopListService.getById(id)
        .then(response => {
          const dataArray = Object.values(response.data);
          setShopListData(dataArray[0]);
        })
        .catch(error => {
          toast.error(t('Error fetching shop list'));
        })
        .finally(() => {
          setLoadingData(false);
          setLoadingBtn(false);
        });
    }
  }, [id, t]);

  const columns = [
    {
      title: t('id'),
      dataIndex: 'id',
      key: 'id',
    },
    {
      title: t('shopname'),
      dataIndex: 'slug',
      key: 'slug',
      render: (slug) => slug || '-',
    },
    {
      title: t('options'),
      key: 'options',
      render: (_, row) => (
        <Space>
          <Button type='primary' title='Add Commission' onClick={() => handleShopClick(row)}>
            {t('Add Commission')}
          </Button>
        </Space>
      ),
    },
  ];

  return (
    <GlobalContainer
      headerTitle={t('Shop List')}
      columns={columns}
    >
      <Table
        columns={columns}
        dataSource={shopListData}
        rowKey={(record) => record.id}
        loading={loadingData}
        pagination={false}
      />
      <AddCommissionModel visibility={modalVisible} handleCancel={handleModalCancel} />
    </GlobalContainer>
  );
};

export default ShowShopList;

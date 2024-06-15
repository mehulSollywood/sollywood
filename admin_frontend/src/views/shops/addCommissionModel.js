import React, { useState } from 'react';
import { Button, Col, Form, InputNumber, Modal, Row } from 'antd';
import { useTranslation } from 'react-i18next';
import pointService from '../../services/points';
import { shallowEqual, useDispatch, useSelector } from 'react-redux';
import { setRefetch } from '../../redux/slices/menu';

export default function AddCommissionModel({ visibility, handleCancel }) {
  const [form] = Form.useForm();
  const { t } = useTranslation();
  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const dispatch = useDispatch();
  const [loading, setLoading] = useState(false);

  const handleOk = () => {
    console.log('ok');
  };

  const onFinish = (values) => {
    console.log('values => ', values);
    setLoading(true);
    const payload = {
      ...values,
      type: 'percent',
    };
    pointService
      .create(payload)
      .then(() => {
        handleCancel();
        dispatch(setRefetch(activeMenu));
      })
      .finally(() => setLoading(false));
  };

  return (
    <Modal
      visible={visibility}
      title={t('add.commission')}
      onOk={handleOk}
      onCancel={handleCancel}
      footer={[
        <Button
          key='save-commission'
          type='primary'
          onClick={() => form.submit()}
          loading={loading}
        >
          {t('save')}
        </Button>,
        <Button key='cancel-commission' type='default' onClick={handleCancel}>
          {t('cancel')}
        </Button>,
      ]}
    >
      <Form form={form} layout='vertical' onFinish={onFinish}>
        <Row gutter={12}>
          <Col span={24}>
            <Form.Item
              label={t('commission')}
              name='commission'
              rules={[
                {
                  required: true,
                  message: t('required'),
                },
              ]}
            >
              <InputNumber min={0} className='w-100' addonAfter='%' />
            </Form.Item>
          </Col>
        </Row>
      </Form>
    </Modal>
  );
}

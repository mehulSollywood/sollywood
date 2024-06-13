import React, { useState, useEffect } from 'react';
import { Button, Card, Col, Form, Input, Row, Select, Switch } from 'antd';
import { toast } from 'react-toastify';
import { useNavigate } from 'react-router-dom';
import LanguageList from '../../components/language-list';
import { shallowEqual, useDispatch, useSelector } from 'react-redux';
import { removeFromMenu, setMenuData } from '../../redux/slices/menu';
import birthdayService from '../../services/category';
import { fetchCategories } from '../../redux/slices/category';
import { useTranslation } from 'react-i18next';

const BirthdayCasebackAdd = () => {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const dispatch = useDispatch();
  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const [form] = Form.useForm();
  const [loadingBtn, setLoadingBtn] = useState(false);
  const [error, setError] = useState(null);

  useEffect(() => {
    return () => {
      const data = form.getFieldsValue(true);
      dispatch(setMenuData({ activeMenu, data }));
    };
  }, []);
 
  const onFinish = (value) => {
    console.log("birthday",value);
    setLoadingBtn(true);
    const body = {
      birthdayCaseback: parseInt(value.birthdayCaseback),
    };
    const nextUrl = '/dashboard';
    birthdayService
      .create(body)
      .then(() => {
        toast.success(t('successfully.created'));
        dispatch(removeFromMenu({ ...activeMenu, nextUrl }));
        dispatch(fetchCategories());
        navigate(`/${nextUrl}`);
      })
      .catch((err) => setError(err.response.data.params))
      .finally(() => setLoadingBtn(false));
  };

  const onFinishFailed = (values) => console.log(values);



  return (
    <Card title={t('add.birthdayCashback')}>
      <Form
        name='basic'
        layout='vertical'
        onFinish={onFinish}
        form={form}
        onFinishFailed={onFinishFailed}
      >
        <Row gutter={12}>
        
          <Col span={12}>
            <Form.Item
              label={t('Birthday Cashback')}
              name='birthdayCaseback'
              rules={[{ required: true, message: t('required') }]}

            >
              <Input />
            </Form.Item>
          </Col>
        </Row>
        <Button type='primary' htmlType='submit' loading={loadingBtn}>
          {t('submit')}
        </Button>
      </Form>
    </Card>
  );
};
export default BirthdayCasebackAdd;

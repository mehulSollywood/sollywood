import React, { useEffect } from 'react';
import { Radio } from 'antd';
import languagesService from '../services/languages';
import { shallowEqual, useDispatch, useSelector } from 'react-redux';
import { setDefaultLanguage, setLangugages } from '../redux/slices/formLang';

const LanguageList = () => {
  const dispatch = useDispatch();
  const { languages, defaultLang } = useSelector(
    (state) => state.formLang,
    shallowEqual
  );

  const fetchLanguages = () => {
    languagesService.getAllActive().then(({ data }) => {
      dispatch(setLangugages(data));
    });
  };

  useEffect(() => {
    if (!languages.length) {
      fetchLanguages();
    }
  }, []);

  const onChangeLanguage = ({ target: { value } }) => {
    dispatch(setDefaultLanguage(value));
  };

  return (
    <>
      <Radio.Group
        value={defaultLang}
        onChange={onChangeLanguage}
        className='float-right'
        buttonStyle='solid'
      >
        {languages.map((item) => {
          return (
            <Radio.Button value={item.locale} key={item.id}>
              {item.title}
            </Radio.Button>
          );
        })}
      </Radio.Group>
    </>
  );
};

export default LanguageList;

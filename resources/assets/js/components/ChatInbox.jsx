import React, { Component } from 'react';

class ChatInbox extends Component {
  render() {
    return (
        <div className="__inboxContainer">
            <div className="row">
              <div className="col-md-4 __messageContainer">
                <div className="__searchContainer">
                  <input
                    type="text"
                    name="inputMsg"
                    placeholder="Search..."
                  />
                  <button className="__searchButton">
                    Search
                  </button>
                </div>
                <div className="__messages">
                  <div className="__messageElements">
                    <div className="__title">Jamee</div>
                    <div className="__description">This is a new message</div>
                  </div>
                  <div className="__messageElements">
                    <div className="__title">Imran</div>
                    <div className="__description">This is another new message</div>
                  </div>
                </div>
              </div>
              <div className="col-md-8 __chatContainer">
                <div className="__chats">
                  <ul>
                    <li className="__botMessage"><span>1asd</span></li>
                    <li className="__humanMessage"><span>2asd</span></li>
                  </ul>
                </div>
                <div className="__inputContainer">
                  <input
                    type="text"
                    name="inputMsg"
                    placeholder="Enter Message"
                  />
                  <div className="__buttonContainer">
                    <button className="__sendButton">
                      Send
                    </button>
                  </div>
                </div>
              </div>
            </div>
        </div>
    );
  }
}

export default ChatInbox;